# Sistema de Notas Fiscais - Manual de Uso

## 🎯 Como Funciona

### Vendas no Balcão (NFC-e)
1. **Faça uma venda normalmente** no sistema POS
2. **Ao finalizar a venda**, aparecerá um **MODAL** perguntando:
   - "**Emitir Nota Fiscal?**"
   - Opções: **"Sim, Emitir Nota Fiscal"** ou **"Não, Apenas Cupom"**

3. **Se escolher "Sim"**: 
   - ✅ Gera NFC-e automaticamente
   - ✅ Salva PDF em `/uploads/notas_fiscais/`
   - ✅ Registra nos logs do sistema

4. **Se escolher "Não"**: 
   - ✅ Finaliza venda normal com cupom atual

### Vendas WooCommerce (NF-e)
- **Automático**: Toda venda do WooCommerce gera NF-e automaticamente
- Status: `processing` ou `completed`

## 📋 Como Ver as Notas Fiscais

### Opção 1: Link na Tela de Vendas
1. Acesse **Vendas** no menu principal
2. Clique no botão **"Notas Fiscais"** (verde) ao lado de "Vendas Suspensas"
3. Abrirá em nova aba com lista de todas as notas

### Opção 2: URL Direta
- Acesse: `http://localhost/loja-tlmotos/index.php/sales/listar_notas`

### Na Lista de Notas você pode:
- **Ver PDF**: Clique em "Ver PDF" para abrir no navegador
- **Download**: Clique em "Download" para baixar o arquivo
- **Filtrar**: Use os cabeçalhos da tabela para ordenar por data, nome, etc.

## 🔧 Teste do Sistema

### URL para Testes
- **Interface de Teste**: `http://localhost/loja-tlmotos/index.php/teste_nf`
- **Gerenciar Notas**: `http://localhost/loja-tlmotos/index.php/notas_fiscais`

## 📂 Onde Ficam os Arquivos

### PDFs das Notas
- **Localização**: `c:\xampp\htdocs\loja-tlmotos\uploads\notas_fiscais\`
- **Formato**: `NFC-e_VENDAID_DATA.pdf` ou `NF-e_ORDERID_DATA.pdf`

### Logs do Sistema
- **Localização**: `c:\xampp\htdocs\loja-tlmotos\application\logs\`
- **Buscar por**: "NFC-e gerada" ou "NFe gerada"

## ⚙️ Configurações

### Ambiente Atual
- **Desenvolvimento**: Mostra todos os erros
- **Produção**: Oculta erros (mais seguro)

Para mudar: Edite `index.php` linha 21
```php
define('ENVIRONMENT', 'development'); // ou 'production'
```

## 🚨 Troubleshooting

### Se não aparece o modal:
1. Verifique se JavaScript está habilitado
2. Abra F12 no navegador e veja erros no Console
3. Teste com ambiente em 'development'

### Se não gera nota fiscal:
1. Verifique logs em `/application/logs/`
2. Teste a interface: `/teste_nf`
3. Verifique se pasta `/uploads/notas_fiscais/` existe e tem permissão de escrita

### Se não consegue ver as notas:
1. Acesse diretamente: `/sales/listar_notas`
2. Verifique se arquivos existem em `/uploads/notas_fiscais/`
3. Teste com usuário administrador

## 📞 Próximos Passos (Opcional)

1. **Configurar SMTP** para envio automático por email
2. **Integração SEFAZ** para homologação oficial
3. **Customizar templates** das notas fiscais
4. **Adicionar QR Code** nas notas

---

## 💡 Resumo Rápido

1. **Venda → Modal aparece → Escolha "Sim" ou "Não"**
2. **Ver notas → Botão "Notas Fiscais" na tela de vendas**
3. **Testar → Acesse `/teste_nf` se der problema**

**Sistema funcionando perfeitamente! ✅**
