<?php
require_once("secure_area.php");

class NotasFiscais extends Secure_area
{
    function __construct()
    {
        parent::__construct('notas_fiscais');
        $this->load->library('NotaFiscalLibrary');
    }

    function index()
    {
        $data['nfes'] = $this->NotaFiscalLibrary->listNotasFiscais('NFe');
        $data['nfces'] = $this->NotaFiscalLibrary->listNotasFiscais('NFCe');
        $this->load->view('notas_fiscais/manage', $data);
    }
    
    /**
     * Gerar NFC-e manual para uma venda específica
     */
    function gerar_nfce($sale_id)
    {
        $this->load->model('Sale');
        $this->load->model('Customer');
        
        // Buscar dados da venda
        $sale_info = $this->Sale->get_info($sale_id)->row();
        if (!$sale_info) {
            show_error('Venda não encontrada');
        }
        
        // Buscar itens da venda
        $sale_items = $this->Sale->get_sale_items($sale_id)->result();
        
        // Preparar dados
        $sale_data = ['sale_id' => $sale_id];
        $customer_data = null;
        
        if ($sale_info->customer_id) {
            $customer = $this->Customer->get_info($sale_info->customer_id);
            $customer_data = [
                'nome' => $customer->first_name . ' ' . $customer->last_name
            ];
        }
        
        $items = [];
        foreach ($sale_items as $item) {
            $items[] = [
                'codigo' => $item->item_id,
                'descricao' => $item->description,
                'quantidade' => $item->quantity_purchased,
                'valor_unitario' => $item->item_unit_price
            ];
        }
        
        // Gerar NFC-e
        $result = $this->NotaFiscalLibrary->generateNFCeSale($sale_data, $customer_data, $items);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', 'NFC-e gerada com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao gerar NFC-e: ' . $result['error']);
        }
        
        redirect('NotasFiscais');
    }
    
    /**
     * Download de nota fiscal
     */
    function download($filename)
    {
        $filepath = FCPATH . 'uploads/notas_fiscais/' . $filename;
        
        if (file_exists($filepath)) {
            $this->load->helper('download');
            force_download($filename, file_get_contents($filepath));
        } else {
            show_404();
        }
    }
}
