<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '../vendor/autoload.php';

use NFePHP\NFe\Tools;
use NFePHP\NFe\Make;
use NFePHP\DA\NFe\Danfe;
use NFePHP\DA\NFCe\Danfce;
use NFePHP\NFe\Common\Standardize;

class NFePHPLibrary 
{
    private $tools;
    private $config;
    private $CI;
    
    public function __construct()
    {
        $this->CI = &get_instance();
        
        // Carrega configurações da empresa
        $this->config = $this->loadConfig();
        
        try {
            $this->tools = new Tools(json_encode($this->config));
            $this->tools->model('55'); // Modelo 55 para NF-e
        } catch (Exception $e) {
            log_message('error', 'Erro ao inicializar NFePHP: ' . $e->getMessage());
        }
    }
    
    private function loadConfig()
    {
        // Configurações base - você deve ajustar conforme seus dados
        return [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => 2, // 1-Produção, 2-Homologação
            "razaosocial" => "TL MOTOS LTDA",
            "nomefantasia" => "TL MOTOS",
            "cnpj" => "00000000000000", // Substitua pelo CNPJ real
            "ie" => "000000000", // Substitua pela IE real
            "im" => "",
            "iest" => "",
            "crt" => 3, // 1-Simples Nacional, 2-Simples Excessivo, 3-Normal
            "logradouro" => "RUA EXEMPLO",
            "numero" => "123",
            "complemento" => "",
            "bairro" => "CENTRO",
            "municipio" => "CIDADE EXEMPLO",
            "cmun" => "0000000", // Código do município
            "uf" => "SP",
            "cep" => "00000000",
            "telefone" => "(11) 99999-9999",
            "email" => "contato@tlmotos.com.br",
            "certPfxName" => "certificado.pfx",
            "certPassword" => "", // Senha do certificado
            "certPfxPath" => APPPATH . 'certificates/',
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => "", // Código de Segurança do Contribuinte
            "CSCid" => "" // Identificador do CSC
        ];
    }
    
    /**
     * Gera NF-e modelo 55 para vendas do WooCommerce
     */
    public function generateNFe($sale_data, $customer_data, $items)
    {
        try {
            $make = new Make();
            
            // Identificação da NF-e
            $make->taginfNFe([
                'versao' => '4.00',
                'Id' => null,
                'pk_nItem' => null
            ]);
            
            // Dados da nota
            $make->tagide([
                'cUF' => '35', // Código da UF
                'cNF' => str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'natOp' => 'Venda de mercadoria',
                'mod' => '55',
                'serie' => '1',
                'nNF' => $sale_data['sale_id'],
                'dhEmi' => date('Y-m-d\TH:i:sP'),
                'tpNF' => '1',
                'idDest' => '1', // 1-Operação interna
                'cMunFG' => $this->config['cmun'],
                'tpImp' => '1',
                'tpEmis' => '1',
                'cDV' => '0',
                'tpAmb' => $this->config['tpAmb'],
                'finNFe' => '1',
                'indFinal' => '1',
                'indPres' => '1',
                'procEmi' => '0',
                'verProc' => '1.0'
            ]);
            
            // Emitente
            $make->tagemit([
                'cnpj' => $this->config['cnpj'],
                'xNome' => $this->config['razaosocial'],
                'xFant' => $this->config['nomefantasia'],
                'ie' => $this->config['ie'],
                'crt' => $this->config['crt']
            ]);
            
            $make->tagenderEmit([
                'xLgr' => $this->config['logradouro'],
                'nro' => $this->config['numero'],
                'xCpl' => $this->config['complemento'],
                'xBairro' => $this->config['bairro'],
                'cMun' => $this->config['cmun'],
                'xMun' => $this->config['municipio'],
                'uf' => $this->config['uf'],
                'cep' => $this->config['cep'],
                'cPais' => '1058',
                'xPais' => 'Brasil',
                'fone' => preg_replace('/[^0-9]/', '', $this->config['telefone'])
            ]);
            
            // Destinatário
            $make->tagdest([
                'cnpj' => isset($customer_data['cnpj']) ? $customer_data['cnpj'] : null,
                'cpf' => isset($customer_data['cpf']) ? $customer_data['cpf'] : null,
                'xNome' => $customer_data['nome'],
                'indIEDest' => '9',
                'email' => $customer_data['email']
            ]);
            
            $make->tagenderDest([
                'xLgr' => $customer_data['endereco'],
                'nro' => $customer_data['numero'],
                'xBairro' => $customer_data['bairro'],
                'cMun' => $customer_data['cod_municipio'],
                'xMun' => $customer_data['municipio'],
                'uf' => $customer_data['uf'],
                'cep' => $customer_data['cep'],
                'cPais' => '1058',
                'xPais' => 'Brasil'
            ]);
            
            // Produtos
            $totalNF = 0;
            foreach ($items as $index => $item) {
                $valorTotal = $item['quantidade'] * $item['valor_unitario'];
                $totalNF += $valorTotal;
                
                $make->tagprod([
                    'nItem' => $index + 1,
                    'cProd' => $item['codigo'],
                    'cEAN' => 'SEM GTIN',
                    'xProd' => $item['descricao'],
                    'ncm' => $item['ncm'] ?? '99999999',
                    'cest' => $item['cest'] ?? '',
                    'cfop' => '5102',
                    'uCom' => 'UN',
                    'qCom' => $item['quantidade'],
                    'vUnCom' => number_format($item['valor_unitario'], 2, '.', ''),
                    'vProd' => number_format($valorTotal, 2, '.', ''),
                    'cEANTrib' => 'SEM GTIN',
                    'uTrib' => 'UN',
                    'qTrib' => $item['quantidade'],
                    'vUnTrib' => number_format($item['valor_unitario'], 2, '.', ''),
                    'indTot' => '1'
                ]);
                
                // ICMS
                $make->tagicms([
                    'nItem' => $index + 1,
                    'orig' => '0',
                    'cst' => '102',
                    'vbc' => '0.00',
                    'picms' => '0.00',
                    'vicms' => '0.00'
                ]);
                
                // PIS
                $make->tagpis([
                    'nItem' => $index + 1,
                    'cst' => '07',
                    'vbc' => '0.00',
                    'ppis' => '0.00',
                    'vpis' => '0.00'
                ]);
                
                // COFINS
                $make->tagcofins([
                    'nItem' => $index + 1,
                    'cst' => '07',
                    'vbc' => '0.00',
                    'pcofins' => '0.00',
                    'vcofins' => '0.00'
                ]);
            }
            
            // Total
            $make->tagicmstot([
                'vbc' => '0.00',
                'vicms' => '0.00',
                'vbcst' => '0.00',
                'vst' => '0.00',
                'vprod' => number_format($totalNF, 2, '.', ''),
                'vfrete' => '0.00',
                'vseg' => '0.00',
                'vdesc' => '0.00',
                'vii' => '0.00',
                'vipi' => '0.00',
                'vpis' => '0.00',
                'vcofins' => '0.00',
                'voutro' => '0.00',
                'vnf' => number_format($totalNF, 2, '.', ''),
                'vtottrib' => '0.00'
            ]);
            
            // Transporte
            $make->tagtransp(['modFrete' => '9']);
            
            // Pagamento
            $make->tagpag(['vTroco' => '0.00']);
            
            $make->tagdetPag([
                'nItem' => 1,
                'indPag' => '1',
                'tPag' => '01', // Dinheiro
                'vPag' => number_format($totalNF, 2, '.', ''),
                'indPag' => '0'
            ]);
            
            // Informações adicionais
            $make->taginfAdic([
                'infCpl' => 'Venda realizada através do sistema PDV TL Motos'
            ]);
            
            $xml = $make->getXML();
            
            // Assinar a NF-e
            $xmlSigned = $this->tools->signNFe($xml);
            
            return [
                'success' => true,
                'xml' => $xmlSigned,
                'chave' => $make->getChave()
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao gerar NF-e: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Gera NFC-e modelo 65 para vendas no balcão
     */
    public function generateNFCe($sale_data, $items)
    {
        try {
            $this->tools->model('65'); // Modelo 65 para NFC-e
            
            $make = new Make();
            
            // Identificação da NFC-e
            $make->taginfNFe([
                'versao' => '4.00',
                'Id' => null,
                'pk_nItem' => null
            ]);
            
            // Dados da nota
            $make->tagide([
                'cUF' => '35',
                'cNF' => str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT),
                'natOp' => 'Venda',
                'mod' => '65',
                'serie' => '1',
                'nNF' => $sale_data['sale_id'],
                'dhEmi' => date('Y-m-d\TH:i:sP'),
                'tpNF' => '1',
                'idDest' => '1',
                'cMunFG' => $this->config['cmun'],
                'tpImp' => '4', // NFC-e
                'tpEmis' => '1',
                'cDV' => '0',
                'tpAmb' => $this->config['tpAmb'],
                'finNFe' => '1',
                'indFinal' => '1',
                'indPres' => '1',
                'procEmi' => '0',
                'verProc' => '1.0'
            ]);
            
            // Emitente
            $make->tagemit([
                'cnpj' => $this->config['cnpj'],
                'xNome' => $this->config['razaosocial'],
                'xFant' => $this->config['nomefantasia'],
                'ie' => $this->config['ie'],
                'crt' => $this->config['crt']
            ]);
            
            $make->tagenderEmit([
                'xLgr' => $this->config['logradouro'],
                'nro' => $this->config['numero'],
                'xCpl' => $this->config['complemento'],
                'xBairro' => $this->config['bairro'],
                'cMun' => $this->config['cmun'],
                'xMun' => $this->config['municipio'],
                'uf' => $this->config['uf'],
                'cep' => $this->config['cep'],
                'cPais' => '1058',
                'xPais' => 'Brasil'
            ]);
            
            // Produtos
            $totalNF = 0;
            foreach ($items as $index => $item) {
                $valorTotal = $item['quantidade'] * $item['valor_unitario'];
                $totalNF += $valorTotal;
                
                $make->tagprod([
                    'nItem' => $index + 1,
                    'cProd' => $item['codigo'],
                    'cEAN' => 'SEM GTIN',
                    'xProd' => $item['descricao'],
                    'ncm' => $item['ncm'] ?? '99999999',
                    'cfop' => '5102',
                    'uCom' => 'UN',
                    'qCom' => $item['quantidade'],
                    'vUnCom' => number_format($item['valor_unitario'], 2, '.', ''),
                    'vProd' => number_format($valorTotal, 2, '.', ''),
                    'cEANTrib' => 'SEM GTIN',
                    'uTrib' => 'UN',
                    'qTrib' => $item['quantidade'],
                    'vUnTrib' => number_format($item['valor_unitario'], 2, '.', ''),
                    'indTot' => '1'
                ]);
                
                // ICMS Simples Nacional
                $make->tagicms([
                    'nItem' => $index + 1,
                    'orig' => '0',
                    'csosn' => '102'
                ]);
                
                // PIS
                $make->tagpis([
                    'nItem' => $index + 1,
                    'cst' => '99',
                    'vbc' => '0.00',
                    'ppis' => '0.00',
                    'vpis' => '0.00'
                ]);
                
                // COFINS
                $make->tagcofins([
                    'nItem' => $index + 1,
                    'cst' => '99',
                    'vbc' => '0.00',
                    'pcofins' => '0.00',
                    'vcofins' => '0.00'
                ]);
            }
            
            // Total
            $make->tagicmstot([
                'vbc' => '0.00',
                'vicms' => '0.00',
                'vbcst' => '0.00',
                'vst' => '0.00',
                'vprod' => number_format($totalNF, 2, '.', ''),
                'vfrete' => '0.00',
                'vseg' => '0.00',
                'vdesc' => '0.00',
                'vii' => '0.00',
                'vipi' => '0.00',
                'vpis' => '0.00',
                'vcofins' => '0.00',
                'voutro' => '0.00',
                'vnf' => number_format($totalNF, 2, '.', ''),
                'vtottrib' => '0.00'
            ]);
            
            // Transporte
            $make->tagtransp(['modFrete' => '9']);
            
            // Pagamento
            $make->tagpag(['vTroco' => '0.00']);
            
            $make->tagdetPag([
                'nItem' => 1,
                'indPag' => '1',
                'tPag' => '01',
                'vPag' => number_format($totalNF, 2, '.', ''),
                'indPag' => '0'
            ]);
            
            $xml = $make->getXML();
            
            // Assinar a NFC-e
            $xmlSigned = $this->tools->signNFe($xml);
            
            return [
                'success' => true,
                'xml' => $xmlSigned,
                'chave' => $make->getChave()
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao gerar NFC-e: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Transmite a NF-e para a SEFAZ
     */
    public function transmitNFe($xml)
    {
        try {
            $response = $this->tools->sefazEnviaLote([$xml], 1);
            
            $standardize = new Standardize();
            $std = $standardize->toStd($response);
            
            if ($std->cStat == 103) {
                // Lote recebido com sucesso
                $recibo = $std->infRec->nRec;
                
                // Consultar recibo
                sleep(2);
                $responseConsulta = $this->tools->sefazConsultaRecibo($recibo);
                $stdConsulta = $standardize->toStd($responseConsulta);
                
                if ($stdConsulta->cStat == 104 && $stdConsulta->protNFe->infProt->cStat == 100) {
                    return [
                        'success' => true,
                        'protocolo' => $stdConsulta->protNFe->infProt->nProt,
                        'xml_autorizada' => $this->tools->addProtocol($xml, $responseConsulta)
                    ];
                }
            }
            
            return [
                'success' => false,
                'error' => 'Erro na transmissão: ' . $std->xMotivo
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao transmitir NF-e: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Gera PDF da DANFE
     */
    public function generateDANFE($xml, $tipo = 'nfe')
    {
        try {
            $logoPath = APPPATH . '../images/logo.png';
            
            if ($tipo == 'nfce') {
                $danfce = new Danfce($xml);
                $danfce->logoParameters($logoPath, 'C', 90, 90);
                $pdf = $danfce->render();
            } else {
                $danfe = new Danfe($xml);
                $danfe->logoParameters($logoPath, 'C', 90, 90);
                $pdf = $danfe->render();
            }
            
            return [
                'success' => true,
                'pdf' => $pdf
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Erro ao gerar DANFE: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Salva XML e PDF em diretório
     */
    public function saveFiles($xml, $pdf, $chave, $tipo = 'nfe')
    {
        $folder = APPPATH . '../uploads/notas_fiscais/' . $tipo . '/';
        
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        
        $xmlFile = $folder . $chave . '.xml';
        $pdfFile = $folder . $chave . '.pdf';
        
        file_put_contents($xmlFile, $xml);
        file_put_contents($pdfFile, $pdf);
        
        return [
            'xml_path' => $xmlFile,
            'pdf_path' => $pdfFile
        ];
    }
}
