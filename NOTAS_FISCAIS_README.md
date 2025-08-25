# Sistema de Notas Fiscais - TL Motos

## 📋 Visão Geral

Este sistema foi implementado para gerar automaticamente notas fiscais para seu PDV TL Motos:

- **NF-e (Modelo 55)**: Para vendas do WooCommerce (enviada por email)
- **NFC-e (Modelo 65)**: Para vendas no balcão/PDV

## ⚙️ Configuração Inicial

### 1. Certificado Digital

1. Coloque seu certificado digital `.pfx` na pasta: `application/certificates/`
2. Edite o arquivo `application/libraries/NotaFiscalLibrary.php`
3. Configure os dados da empresa na função `loadConfig()`:
   - CNPJ, IE, Razão Social
   - Endereço completo
   - Senha do certificado

### 2. Configuração de Email

Edite o arquivo `application/libraries/NotaFiscalLibrary.php` na função `sendNFeByEmail()`:

```php
$config = [
    'protocol' => 'smtp',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'seu-email@gmail.com',
    'smtp_pass' => 'sua-senha-app',
    'smtp_crypto' => 'tls',
    'mailtype' => 'html',
    'charset' => 'utf-8'
];
```

### 3. Habilitar Extensão SOAP

Para usar o NFePHP completo, habilite a extensão SOAP no PHP:

1. Abra o arquivo `C:\xampp\php\php.ini`
2. Descomente a linha: `;extension=soap`
3. Reinicie o Apache

### 4. Ambiente de Produção

No arquivo `application/libraries/NotaFiscalLibrary.php`, altere:

```php
"tpAmb" => 1, // 1-Produção, 2-Homologação
```

## 🚀 Como Funciona

### Vendas WooCommerce → NF-e
1. Cliente faz pedido na loja online
2. Webhook chama o sistema via `controllers/wh.php`
3. Sistema gera NF-e automaticamente
4. NF-e é enviada por email para o cliente
5. PDF é salvo em `uploads/notas_fiscais/`

### Vendas Balcão → NFC-e
1. Vendedor finaliza venda no PDV
2. Sistema gera NFC-e automaticamente
3. PDF é salvo em `uploads/notas_fiscais/`
4. NFC-e pode ser impressa ou enviada ao cliente

## 📁 Estrutura de Arquivos

```
application/
├── controllers/
│   ├── NotasFiscais.php          # Gerenciamento de notas
│   ├── sales.php                 # PDV (modificado)
│   └── wh.php                    # Webhook WooCommerce (modificado)
├── libraries/
│   └── NotaFiscalLibrary.php     # Biblioteca principal
├── views/
│   └── notas_fiscais/
│       └── manage.php            # Interface de gerenciamento
└── certificates/
    └── certificado.pfx           # Seu certificado (colocar aqui)

uploads/
└── notas_fiscais/               # PDFs gerados
    ├── NFe_123_2025-01-21.html
    └── NFCe_456_2025-01-21.html
```

## 🔧 Gerenciamento

Acesse: `http://seusite.com/NotasFiscais`

- Visualizar notas geradas
- Fazer download dos PDFs
- Gerar NFC-e manualmente
- Estatísticas

## 📝 Personalização

### Alterar Layout das Notas

Edite a função `generateNFeHTML()` em `NotaFiscalLibrary.php`

### Adicionar Campos

Modifique as funções:
- `generateNFeWooCommerce()` - Para NF-e
- `generateNFCeSale()` - Para NFC-e

### Configurar NCM

Adicione NCM específico para cada produto no banco de dados ou configure um padrão.

## 🐛 Troubleshooting

### Email não envia
1. Verifique configurações SMTP
2. Use senha de app do Gmail
3. Verifique logs em `application/logs/`

### Certificado não funciona
1. Verifique se o arquivo está em `application/certificates/`
2. Confirme a senha do certificado
3. Teste em ambiente de homologação primeiro

### Notas não são geradas
1. Verifique logs em `application/logs/`
2. Confirme se as bibliotecas foram instaladas via Composer
3. Teste a geração manual primeiro

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique os logs do sistema
2. Teste em ambiente de homologação
3. Consulte a documentação do NFePHP

## 🔄 Próximas Implementações

- [ ] Integração completa com NFePHP para transmissão SEFAZ
- [ ] Geração de QR Code para NFC-e
- [ ] Cancelamento de notas
- [ ] Relatórios de notas fiscais
- [ ] Backup automático das notas
