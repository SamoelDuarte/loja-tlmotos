<?php

class TesteNFSimples extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        echo "<h1>🧾 Teste Simples de Notas Fiscais - TL Motos</h1>";
        echo "<p><strong>Data:</strong> " . date('d/m/Y H:i:s') . "</p>";
        echo "<hr>";
        
        try {
            // Instanciar a biblioteca diretamente
            require_once APPPATH . 'libraries/NotaFiscalLibrary.php';
            $notafiscal = new NotaFiscalLibrary();
            
            // Dados de teste
            $sale_data = ['sale_id' => '999'];
            $customer_data = [
                'nome' => 'João Silva - Cliente Teste',
                'email' => 'teste@email.com'
            ];
            $items = [
                [
                    'codigo' => 'MOTO001',
                    'descricao' => 'Peça para Honda CB 600F',
                    'quantidade' => 2,
                    'valor_unitario' => 150.00
                ],
                [
                    'codigo' => 'MOTO002', 
                    'descricao' => 'Óleo Motul 10W40',
                    'quantidade' => 1,
                    'valor_unitario' => 45.90
                ]
            ];
            
            echo "<h2>🧾 Teste 1: Gerando NFC-e (Venda Balcão)...</h2>";
            echo "<p><em>Simulando venda no PDV...</em></p>";
            
            $result_nfce = $notafiscal->generateNFCeSale($sale_data, $customer_data, $items);
            
            if ($result_nfce['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: green; font-weight: bold; margin: 0 0 10px 0;'>✅ NFC-e gerada com sucesso!</p>";
                echo "<p><strong>Tipo:</strong> " . $result_nfce['tipo'] . " (Modelo 65)</p>";
                echo "<p><strong>Arquivo:</strong> " . basename($result_nfce['pdf_path']) . "</p>";
                echo "<p><a href='" . base_url('uploads/notas_fiscais/' . basename($result_nfce['pdf_path'])) . "' target='_blank' style='color: #007bff;'>📄 Visualizar NFC-e</a></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: red; font-weight: bold; margin: 0 0 10px 0;'>❌ Erro ao gerar NFC-e:</p>";
                echo "<p style='color: red; margin: 0;'>" . $result_nfce['error'] . "</p>";
                echo "</div>";
            }
            
            echo "<hr>";
            
            echo "<h2>📧 Teste 2: Gerando NF-e (Venda WooCommerce)...</h2>";
            echo "<p><em>Simulando pedido da loja online...</em></p>";
            
            $order_data = ['id' => '12345'];
            $customer_data_nfe = [
                'nome' => 'Maria Santos - Cliente Online',
                'email' => 'maria@exemplo.com',
                'endereco' => 'Rua das Flores, 456',
                'numero' => '456',
                'bairro' => 'Jardim América',
                'municipio' => 'São Paulo',
                'uf' => 'SP',
                'cep' => '01310100',
                'cod_municipio' => '3550308'
            ];
            
            $result_nfe = $notafiscal->generateNFeWooCommerce($order_data, $customer_data_nfe, $items);
            
            if ($result_nfe['success']) {
                echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: green; font-weight: bold; margin: 0 0 10px 0;'>✅ NF-e gerada com sucesso!</p>";
                echo "<p><strong>Tipo:</strong> " . $result_nfe['tipo'] . " (Modelo 55)</p>";
                echo "<p><strong>Arquivo:</strong> " . basename($result_nfe['pdf_path']) . "</p>";
                echo "<p><strong>Email:</strong> " . ($result_nfe['email_sent'] ? '✅ Enviado' : '⚠️ Não enviado (configure email)') . "</p>";
                echo "<p><a href='" . base_url('uploads/notas_fiscais/' . basename($result_nfe['pdf_path'])) . "' target='_blank' style='color: #007bff;'>📄 Visualizar NF-e</a></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                echo "<p style='color: red; font-weight: bold; margin: 0 0 10px 0;'>❌ Erro ao gerar NF-e:</p>";
                echo "<p style='color: red; margin: 0;'>" . $result_nfe['error'] . "</p>";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<p style='color: red; font-weight: bold; margin: 0 0 10px 0;'>❌ Erro crítico no teste:</p>";
            echo "<p style='color: red; margin: 0;'>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
        
        echo "<hr>";
        
        // Verificar diretório e arquivos
        $upload_dir = FCPATH . 'uploads/notas_fiscais/';
        echo "<h3>📁 Verificação do Sistema:</h3>";
        
        if (is_dir($upload_dir)) {
            echo "<p style='color: green;'>✅ Diretório de notas fiscais existe</p>";
            
            $files = scandir($upload_dir);
            $nf_files = array_filter($files, function($file) {
                return $file != '.' && $file != '..' && (strpos($file, 'NFe_') === 0 || strpos($file, 'NFCe_') === 0);
            });
            
            if (!empty($nf_files)) {
                echo "<h4>📄 Arquivos encontrados:</h4>";
                echo "<ul>";
                foreach ($nf_files as $file) {
                    $filesize = filesize($upload_dir . $file);
                    echo "<li>";
                    echo "<strong>" . $file . "</strong> ";
                    echo "(" . number_format($filesize / 1024, 1) . " KB) ";
                    echo "<em>" . date('d/m/Y H:i:s', filemtime($upload_dir . $file)) . "</em> ";
                    echo "<a href='" . base_url('uploads/notas_fiscais/' . $file) . "' target='_blank' style='color: #007bff;'>Ver</a>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p style='color: orange;'>⚠️ Nenhuma nota fiscal encontrada ainda</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Diretório de notas fiscais não existe: " . $upload_dir . "</p>";
            echo "<p>Tentando criar...</p>";
            if (mkdir($upload_dir, 0755, true)) {
                echo "<p style='color: green;'>✅ Diretório criado com sucesso!</p>";
            } else {
                echo "<p style='color: red;'>❌ Erro ao criar diretório</p>";
            }
        }
        
        // Verificar se a biblioteca existe
        $lib_path = APPPATH . 'libraries/NotaFiscalLibrary.php';
        if (file_exists($lib_path)) {
            echo "<p style='color: green;'>✅ Biblioteca NotaFiscalLibrary.php encontrada</p>";
        } else {
            echo "<p style='color: red;'>❌ Biblioteca NotaFiscalLibrary.php não encontrada</p>";
        }
        
        echo "<hr>";
        echo "<h3>🔧 Links úteis:</h3>";
        echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";
        echo "<p>• <strong><a href='" . site_url('sales') . "' style='color: #007bff;'>PDV - Sistema de Vendas</a></strong></p>";
        echo "<p>• <a href='" . base_url('uploads/notas_fiscais/') . "' target='_blank' style='color: #007bff;'>Pasta de Notas Fiscais</a></p>";
        echo "<p>• <a href='" . site_url('TesteNFSimples') . "' style='color: #007bff;'>Repetir Teste</a></p>";
        echo "</div>";
        
        echo "<hr>";
        echo "<p><small><strong>Sistema de Notas Fiscais TL Motos</strong> - Teste Simplificado v1.0</small></p>";
    }
}
