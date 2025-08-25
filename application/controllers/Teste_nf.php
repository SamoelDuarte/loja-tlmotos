<?php
require_once("secure_area.php");

class Teste_nf extends Secure_area
{
    private $notafiscal;
    
    function __construct()
    {
        parent::__construct('teste_nf');
    }

    function index()
    {
        // Carregar a biblioteca
        require_once(APPPATH . 'libraries/NotaFiscalLibrary.php');
        $notafiscal = new NotaFiscalLibrary();
        
        echo "<h1>Teste de Geração de Nota Fiscal - TL Motos</h1>";
        echo "<p>Data: " . date('d/m/Y H:i:s') . "</p>";
        echo "<hr>";
        
        try {
            // Dados de teste
            $sale_data = ['sale_id' => '999'];
            $customer_data = [
                'nome' => 'Cliente Teste',
                'email' => 'teste@email.com'
            ];
            $items = [
                [
                    'codigo' => '001',
                    'descricao' => 'Produto Teste',
                    'quantidade' => 1,
                    'valor_unitario' => 100.00
                ]
            ];
            
            echo "<h2>🧾 Testando NFC-e (Modelo 65)...</h2>";
            $result_nfce = $notafiscal->generateNFCeSale($sale_data, $customer_data, $items);
            
            if ($result_nfce['success']) {
                echo "<p style='color: green; font-weight: bold;'>✅ NFC-e gerada com sucesso!</p>";
                echo "<p><strong>Tipo:</strong> " . $result_nfce['tipo'] . "</p>";
                echo "<p><strong>Arquivo:</strong> " . basename($result_nfce['pdf_path']) . "</p>";
                echo "<p><a href='../uploads/notas_fiscais/" . basename($result_nfce['pdf_path']) . "' target='_blank'>📄 Visualizar PDF</a></p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Erro ao gerar NFC-e:</p>";
                echo "<p style='color: red;'>" . $result_nfce['error'] . "</p>";
            }
            
            echo "<hr>";
            
            echo "<h2>📧 Testando NF-e (Modelo 55) + Email...</h2>";
            $order_data = ['id' => '888'];
            $customer_data_nfe = [
                'nome' => 'Cliente WooCommerce Teste',
                'email' => 'teste@tlmotos.com.br',
                'endereco' => 'Rua das Motos',
                'numero' => '123',
                'bairro' => 'Centro',
                'municipio' => 'São Paulo',
                'uf' => 'SP',
                'cep' => '01000000',
                'cod_municipio' => '3550308'
            ];
            
            $result_nfe = $notafiscal->generateNFeWooCommerce($order_data, $customer_data_nfe, $items);
            
            if ($result_nfe['success']) {
                echo "<p style='color: green; font-weight: bold;'>✅ NF-e gerada com sucesso!</p>";
                echo "<p><strong>Tipo:</strong> " . $result_nfe['tipo'] . "</p>";
                echo "<p><strong>Arquivo:</strong> " . basename($result_nfe['pdf_path']) . "</p>";
                echo "<p><strong>Email enviado:</strong> " . ($result_nfe['email_sent'] ? '✅ Sim' : '❌ Não (verifique configuração)') . "</p>";
                echo "<p><a href='../uploads/notas_fiscais/" . basename($result_nfe['pdf_path']) . "' target='_blank'>📄 Visualizar PDF</a></p>";
            } else {
                echo "<p style='color: red; font-weight: bold;'>❌ Erro ao gerar NF-e:</p>";
                echo "<p style='color: red;'>" . $result_nfe['error'] . "</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red; font-weight: bold;'>❌ Erro geral no teste:</p>";
            echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
        }
        
        echo "<hr>";
        
        // Verificar se o diretório existe
        $upload_dir = FCPATH . 'uploads/notas_fiscais/';
        if (is_dir($upload_dir)) {
            echo "<h3>📁 Arquivos gerados:</h3>";
            $files = scandir($upload_dir);
            $nf_files = array_filter($files, function($file) {
                return $file != '.' && $file != '..' && (strpos($file, 'NFe_') === 0 || strpos($file, 'NFCe_') === 0);
            });
            
            if (!empty($nf_files)) {
                echo "<ul>";
                foreach ($nf_files as $file) {
                    echo "<li><a href='../uploads/notas_fiscais/" . $file . "' target='_blank'>" . $file . "</a> (" . date('d/m/Y H:i:s', filemtime($upload_dir . $file)) . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Nenhum arquivo encontrado.</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ Diretório de notas fiscais não existe: " . $upload_dir . "</p>";
        }
        
        echo "<hr>";
        echo "<h3>🔧 Links úteis:</h3>";
        echo "<p>• <a href='NotasFiscais'>Gerenciador de Notas Fiscais</a></p>";
        echo "<p>• <a href='sales'>PDV - Sistema de Vendas</a></p>";
        echo "<p>• <a href='../uploads/notas_fiscais/' target='_blank'>Pasta de Notas Fiscais</a></p>";
        
        echo "<hr>";
        echo "<p><small>Sistema de Notas Fiscais TL Motos - Versão 1.0</small></p>";
    }
}
