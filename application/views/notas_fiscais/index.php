<!DOCTYPE html>
<html>
<head>
    <title>Gerenciador de Notas Fiscais - TL Motos</title>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }
        .table-container {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #34495e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn {
            background-color: #3498db;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-success {
            background-color: #27ae60;
        }
        .btn-success:hover {
            background-color: #229954;
        }
        .nav-links {
            text-align: center;
            margin: 20px 0;
        }
        .nav-links a {
            background: #2c3e50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            margin: 5px;
            border-radius: 4px;
        }
        .empty-message {
            text-align: center;
            color: #7f8c8d;
            font-style: italic;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Gerenciador de Notas Fiscais - TL Motos</h1>
        
        <div class="stats">
            <div class="stat-card">
                <span class="stat-number"><?= count($nfes ?? []) ?></span>
                <span>NF-e Geradas</span>
                <small>(Modelo 55)</small>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= count($nfces ?? []) ?></span>
                <span>NFC-e Geradas</span>
                <small>(Modelo 65)</small>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?= count(($nfes ?? [])) + count(($nfces ?? [])) ?></span>
                <span>Total de Notas</span>
                <small>(Todos os tipos)</small>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="TesteNF">🧪 Testar Sistema</a>
            <a href="sales">💰 PDV - Vendas</a>
            <a href="../uploads/notas_fiscais/" target="_blank">📁 Ver Arquivos</a>
        </div>
        
        <div class="table-container">
            <h2>📧 Notas Fiscais Eletrônicas (NF-e)</h2>
            <?php if (!empty($nfes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th>Data de Criação</th>
                            <th>Tamanho</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nfes as $nfe): ?>
                            <?php 
                                $filepath = FCPATH . 'uploads/notas_fiscais/' . $nfe;
                                $fileinfo = file_exists($filepath) ? stat($filepath) : null;
                            ?>
                            <tr>
                                <td><?= $nfe ?></td>
                                <td><?= $fileinfo ? date('d/m/Y H:i:s', $fileinfo['mtime']) : 'N/A' ?></td>
                                <td><?= $fileinfo ? number_format($fileinfo['size'] / 1024, 1) . ' KB' : 'N/A' ?></td>
                                <td>
                                    <a href="../uploads/notas_fiscais/<?= $nfe ?>" class="btn" target="_blank">📄 Visualizar</a>
                                    <a href="NotasFiscais/download/<?= $nfe ?>" class="btn btn-success">💾 Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <p>Nenhuma NF-e encontrada.</p>
                    <p>As NF-e são geradas automaticamente para pedidos do WooCommerce.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="table-container">
            <h2>🧾 Notas Fiscais de Consumidor (NFC-e)</h2>
            <?php if (!empty($nfces)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Arquivo</th>
                            <th>Data de Criação</th>
                            <th>Tamanho</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($nfces as $nfce): ?>
                            <?php 
                                $filepath = FCPATH . 'uploads/notas_fiscais/' . $nfce;
                                $fileinfo = file_exists($filepath) ? stat($filepath) : null;
                            ?>
                            <tr>
                                <td><?= $nfce ?></td>
                                <td><?= $fileinfo ? date('d/m/Y H:i:s', $fileinfo['mtime']) : 'N/A' ?></td>
                                <td><?= $fileinfo ? number_format($fileinfo['size'] / 1024, 1) . ' KB' : 'N/A' ?></td>
                                <td>
                                    <a href="../uploads/notas_fiscais/<?= $nfce ?>" class="btn" target="_blank">📄 Visualizar</a>
                                    <a href="NotasFiscais/download/<?= $nfce ?>" class="btn btn-success">💾 Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    <p>Nenhuma NFC-e encontrada.</p>
                    <p>As NFC-e são geradas automaticamente para vendas no balcão.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p><small>Sistema de Notas Fiscais TL Motos - Versão 1.0 - <?= date('d/m/Y H:i:s') ?></small></p>
        </div>
    </div>
</body>
</html>
