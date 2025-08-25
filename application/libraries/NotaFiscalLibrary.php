<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class NotaFiscalLibrary 
{
    private $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
    }
    
    /**
     * Gera nota fiscal para venda do WooCommerce (envia por email)
     */
    public function generateNFeWooCommerce($order_data, $customer_data, $items)
    {
        try {
            // Gerar PDF da nota fiscal
            $pdf_content = $this->generateNFePDF($order_data, $customer_data, $items, 'NFe');
            
            // Salvar PDF
            $pdf_path = $this->savePDF($pdf_content, 'NFe_' . $order_data['id'] . '_' . date('Y-m-d'));
            
            // Enviar por email
            $email_sent = $this->sendNFeByEmail($customer_data['email'], $pdf_path, $order_data['id']);
            
            return [
                'success' => true,
                'pdf_path' => $pdf_path,
                'email_sent' => $email_sent,
                'tipo' => 'NFe'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao gerar NFe para WooCommerce: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Gera NFC-e para venda no balcão
     */
    public function generateNFCeSale($sale_data, $customer_data, $items)
    {
        try {
            // Gerar PDF da NFC-e
            $pdf_content = $this->generateNFePDF($sale_data, $customer_data, $items, 'NFCe');
            
            // Salvar PDF
            $pdf_path = $this->savePDF($pdf_content, 'NFCe_' . $sale_data['sale_id'] . '_' . date('Y-m-d'));
            
            return [
                'success' => true,
                'pdf_path' => $pdf_path,
                'tipo' => 'NFCe'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao gerar NFCe: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Gera conteúdo PDF da nota fiscal
     */
    private function generateNFePDF($sale_data, $customer_data, $items, $tipo = 'NFe')
    {
        // Dados da empresa (você deve ajustar conforme seus dados)
        $empresa = [
            'razao_social' => 'TL MOTOS LTDA',
            'nome_fantasia' => 'TL MOTOS',
            'cnpj' => '00.000.000/0000-00',
            'ie' => '000.000.000.000',
            'endereco' => 'Rua Exemplo, 123 - Centro',
            'cidade' => 'Cidade Exemplo - SP',
            'cep' => '00000-000',
            'telefone' => '(11) 99999-9999',
            'email' => 'contato@tlmotos.com.br'
        ];
        
        $html = $this->generateNFeHTML($empresa, $sale_data, $customer_data, $items, $tipo);
        
        // Se você quiser usar uma biblioteca de PDF como TCPDF ou DomPDF
        // Por enquanto, retorna o HTML que pode ser convertido em PDF
        return $html;
    }
    
    /**
     * Gera HTML da nota fiscal
     */
    private function generateNFeHTML($empresa, $sale_data, $customer_data, $items, $tipo)
    {
        $numero_nota = $tipo == 'NFe' ? 'NFe-' . $sale_data['id'] : 'NFCe-' . $sale_data['sale_id'];
        $data_emissao = date('d/m/Y H:i:s');
        $chave_acesso = $this->generateFakeChaveAcesso(); // Chave fictícia para demonstração
        
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $tipo . ' - ' . $numero_nota . '</title>
            <style>
                @media print {
                    body { 
                        margin: 0; 
                        padding: 20px;
                        display: flex;
                        justify-content: center;
                        align-items: flex-start;
                        min-height: 100vh;
                    }
                    .cupom-container {
                        margin: 0 auto;
                    }
                    .no-print { display: none; }
                }
                @media screen {
                    body {
                        background-color: #f0f0f0;
                        display: flex;
                        justify-content: center;
                        align-items: flex-start;
                        min-height: 100vh;
                        padding: 20px;
                        margin: 0;
                    }
                    .cupom-container {
                        background-color: white;
                        box-shadow: 0 0 20px rgba(0,0,0,0.3);
                        border-radius: 5px;
                        padding: 20px;
                    }
                }
                .cupom-container {
                    font-family: "Courier New", monospace; 
                    font-size: 10px; 
                    width: 300px;
                    line-height: 1.2;
                }
                .center { text-align: center; }
                .bold { font-weight: bold; }
                .line { border-bottom: 1px dashed #000; margin: 3px 0; }
                .header { text-align: center; margin-bottom: 10px; }
                .header h1 { margin: 2px 0; font-size: 11px; }
                .header h2 { margin: 2px 0; font-size: 10px; }
                .info-line { margin: 1px 0; }
                .items-table { width: 100%; border-collapse: collapse; margin: 5px 0; }
                .items-table td { padding: 1px 2px; font-size: 9px; }
                .total-line { border-top: 1px dashed #000; margin-top: 5px; padding-top: 3px; }
                .qr-section { text-align: center; margin: 10px 0; font-size: 8px; }
                .footer { font-size: 8px; text-align: center; margin-top: 10px; }
                .space { margin: 5px 0; }
                .cut-line { 
                    border-top: 1px dashed #000; 
                    margin: 20px 0 10px 0; 
                    text-align: center; 
                    font-size: 8px; 
                }
            </style>
        </head>
        <body>
            <div class="cupom-container">
            <div class="header">
                <div class="bold">' . $empresa['nome_fantasia'] . '</div>
                <div>' . $empresa['razao_social'] . '</div>
                <div>CNPJ: ' . $empresa['cnpj'] . '</div>
                <div>' . $empresa['endereco'] . '</div>
                <div>' . $empresa['cidade'] . '</div>
                <div>Tel: ' . $empresa['telefone'] . '</div>
            </div>
            
            <div class="line"></div>
            
            <div class="center bold">
                ' . ($tipo == 'NFe' ? 'NOTA FISCAL ELETRÔNICA' : 'NFC-e') . '<br>
                N° ' . str_pad($sale_data['sale_id'], 6, '0', STR_PAD_LEFT) . ' - SÉRIE 001<br>
                ' . $data_emissao . '
            </div>
            
            <div class="line"></div>';
            
        if ($tipo == 'NFe' && !empty($customer_data)) {
            $html .= '
            <div>
                <div class="bold">CLIENTE:</div>
                <div>' . $customer_data['nome'] . '</div>
                <div>CPF/CNPJ: ' . (isset($customer_data['cnpj']) ? $customer_data['cnpj'] : $customer_data['cpf']) . '</div>
            </div>
            <div class="line"></div>';
        }
        
        $html .= '
            <table class="items-table">
                <tr>
                    <td colspan="4" class="bold center">PRODUTOS/SERVIÇOS</td>
                </tr>';
                
        $total_produtos = 0;
        foreach ($items as $index => $item) {
            $total_item = $item['quantidade'] * $item['valor_unitario'];
            $total_produtos += $total_item;
            
            // Nome do produto (se disponível) ou descrição
            $nome_produto = isset($item['nome']) && !empty($item['nome']) ? $item['nome'] : $item['descricao'];
            
            $html .= '
                <tr>
                    <td colspan="4">' . ($index + 1) . '. ' . strtoupper(substr($nome_produto, 0, 35)) . '</td>
                </tr>
                <tr>
                    <td>QTD: ' . number_format($item['quantidade'], 0) . '</td>
                    <td>UN: R$ ' . number_format($item['valor_unitario'], 2, ',', '.') . '</td>
                    <td colspan="2">TOT: R$ ' . number_format($total_item, 2, ',', '.') . '</td>
                </tr>';
        }
        
        $html .= '
            </table>
            
            <div class="total-line">
                <div class="info-line">QTD. TOTAL ITENS: ' . count($items) . '</div>
                <div class="info-line">SUBTOTAL: R$ ' . number_format($total_produtos, 2, ',', '.') . '</div>
                <div class="info-line">DESCONTO: R$ 0,00</div>
                <div class="info-line bold">TOTAL: R$ ' . number_format($total_produtos, 2, ',', '.') . '</div>
                <div class="info-line">FORMA PAGTO: DINHEIRO</div>
            </div>
            
            <div class="space"></div>
            
            <div class="center" style="font-size: 8px;">
                ICMS: R$ ' . number_format($total_produtos * 0.18, 2, ',', '.') . ' (18%)<br>
                Tributos aprox.: R$ ' . number_format($total_produtos * 0.25, 2, ',', '.') . ' (25%)
            </div>';
            
        if ($tipo == 'NFCe') {
            $html .= '
            <div class="qr-section">
                <div class="line"></div>
                <div class="bold">CONSULTE PELA CHAVE DE ACESSO</div>
                <div>www.nfce.fazenda.sp.gov.br</div>
                <div style="margin: 5px 0;">
                    [QR CODE AQUI]<br>
                    ' . substr($chave_acesso, 0, 22) . '<br>
                    ' . substr($chave_acesso, 22) . '
                </div>
                <div>PROTOCOLO: 135' . date('y') . str_pad($sale_data['sale_id'], 8, '0', STR_PAD_LEFT) . '</div>
            </div>';
        }
        
        $html .= '
            <div class="footer">
                <div class="line"></div>
                <div>Esta NFC-e foi processada pela SEFAZ</div>
                <div>e possui validade jurídica</div>
                <div style="margin-top: 5px;">TL MOTOS - ' . date('d/m/Y H:i') . '</div>
            </div>
            
            <div class="cut-line">
                ✂️ CORTE AQUI ✂️
            </div>
            
            </div> <!-- Fim cupom-container -->
            
            <script type="text/javascript">
                // Auto-imprimir quando a página carregar
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 300);
                };
                
                // Voltar para nova venda após impressão
                window.onafterprint = function() {
                    setTimeout(function() {
                        if (confirm("Impressão concluída. Nova venda?")) {
                            window.location.href = "' . site_url('sales') . '";
                        }
                    }, 1000);
                };
            </script>
        </body>
        </html>';
        
        return $html;
        
        $html .= '
            <div class="footer">
                <p><strong>INFORMAÇÕES COMPLEMENTARES DE INTERESSE DO CONTRIBUINTE:</strong></p>
                <p class="highlight">NOTA FISCAL GERADA PELO SISTEMA PDV TL MOTOS EM ' . strtoupper(date('d/m/Y H:i:s')) . '</p>
                <p>Esta é uma ' . ($tipo == 'NFe' ? 'Nota Fiscal Eletrônica' : 'Nota Fiscal de Consumidor Eletrônica') . ' válida com força de documento fiscal</p>
                ' . ($tipo == 'NFe' ? '<p style="color: #006600;">✓ Enviada por email para: ' . $customer_data['email'] . '</p>' : '') . '
                <p style="margin-top: 10px; font-size: 8px;">Sistema desenvolvido por TL Motos - Documento gerado eletronicamente</p>
            </div>
            
            <script type="text/javascript">
                // Auto-imprimir quando a página carregar
                window.onload = function() {
                    // Dar um tempo para a página carregar completamente
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
                
                // Voltar para nova venda após impressão
                window.onafterprint = function() {
                    if (confirm("Impressão concluída. Deseja fazer nova venda?")) {
                        window.location.href = "' . site_url('sales') . '";
                    }
                };
            </script>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Gera uma chave de acesso fictícia para demonstração
     */
    private function generateFakeChaveAcesso()
    {
        // Gera uma chave de acesso fictícia no formato padrão (44 dígitos)
        $cuf = '35'; // São Paulo
        $aamm = date('ym');
        $cnpj = '00000000000000';
        $mod = '65'; // NFCe
        $serie = '001';
        $nnf = str_pad(rand(1, 999999), 9, '0', STR_PAD_LEFT);
        $tpamb = '2'; // Homologação
        $cemit = str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $dv = rand(0, 9);
        
        return $cuf . $aamm . $cnpj . $mod . $serie . $nnf . $tpamb . $cemit . $dv;
    }
    
    /**
     * Salva PDF no servidor
     */
    private function savePDF($html_content, $filename)
    {
        $upload_path = FCPATH . 'uploads/notas_fiscais/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $filepath = $upload_path . $filename . '.html';
        file_put_contents($filepath, $html_content);
        
        // Retornar caminho relativo para web
        return 'uploads/notas_fiscais/' . $filename . '.html';
    }
    
    /**
     * Envia NFe por email
     */
    public function sendNFeByEmail($email, $pdf_path, $order_id)
    {
        try {
            $this->CI->load->library('email');
            $this->CI->email->clear();
            
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'smtp.gmail.com', // Configure conforme seu provedor
                'smtp_port' => 587,
                'smtp_user' => 'seu-email@gmail.com', // Configure seu email
                'smtp_pass' => 'sua-senha', // Configure sua senha
                'smtp_crypto' => 'tls',
                'mailtype' => 'html',
                'charset' => 'utf-8'
            ];
            
            $this->CI->email->initialize($config);
            
            $this->CI->email->from('contato@tlmotos.com.br', 'TL Motos');
            $this->CI->email->to($email);
            $this->CI->email->subject('Nota Fiscal Eletrônica - Pedido #' . $order_id);
            
            $message = '
                <html>
                <body>
                    <h2>TL Motos - Nota Fiscal Eletrônica</h2>
                    <p>Prezado(a) Cliente,</p>
                    <p>Segue em anexo a Nota Fiscal Eletrônica referente ao seu pedido #' . $order_id . '.</p>
                    <p>Obrigado pela preferência!</p>
                    <br>
                    <p><strong>TL Motos</strong></p>
                    <p>Email: contato@tlmotos.com.br</p>
                    <p>Telefone: (11) 99999-9999</p>
                </body>
                </html>
            ';
            
            $this->CI->email->message($message);
            
            if (file_exists($pdf_path)) {
                $this->CI->email->attach($pdf_path);
            }
            
            if ($this->CI->email->send()) {
                log_message('info', 'NFe enviada por email para: ' . $email . ' - Pedido: ' . $order_id);
                return true;
            } else {
                log_message('error', 'Erro ao enviar email da NFe: ' . $this->CI->email->print_debugger());
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao enviar NFe por email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lista notas fiscais salvas
     */
    public function listNotasFiscais($tipo = null)
    {
        $upload_path = FCPATH . 'uploads/notas_fiscais/';
        $files = [];
        
        if (is_dir($upload_path)) {
            $scan = scandir($upload_path);
            foreach ($scan as $file) {
                if ($file != '.' && $file != '..') {
                    if ($tipo) {
                        if (strpos($file, $tipo) !== false) {
                            $files[] = $file;
                        }
                    } else {
                        $files[] = $file;
                    }
                }
            }
        }
        
        return $files;
    }
}
