<?php
require 'conectar.php';

// Busca todas as bancas cadastradas no banco
$stmt = $pdo->query("SELECT banca, nome_banca, codigo, valor FROM QRcodes ORDER BY banca ASC");
$bancas = $stmt->fetchAll();

// Descobre automaticamente o endereço base do seu projeto
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$pasta = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url_base = $protocolo . "://" . $host . $pasta . "/resgatar.php?codigo=";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de QR Codes - DBverse</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            padding: 30px;
            color: #333;
        }
        .topo {
            max-width: 1000px;
            margin: 0 auto 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            justify-content: space-between;
        }
        .topo input {
            padding: 10px;
            width: 360px;
            max-width: 100%;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
            margin-top: 5px;
        }
        .aviso-localhost {
            width: 100%;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 13px;
            display: none;
        }
        .btn {
            background-color: #5c1623;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn:hover { background-color: #7a1e2f; }
        .btn-print { background-color: #2b2b2b; }

        .grade {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 25px;
        }
        .card {
            background: #ffffff;
            border: 3px solid #5c1623;
            border-radius: 16px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08);
            page-break-inside: avoid;
        }
        .card h2 {
            color: #5c1623;
            font-size: 22px;
            margin-bottom: 5px;
        }
        .valor {
            display: inline-block;
            background: #ffe8ec;
            color: #5c1623;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            margin-bottom: 18px;
        }
        .qr-box {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 10px 0 15px 0;
            min-height: 180px;
        }
        .qr-box img {
            width: 180px;
            height: 180px;
            border: 1px solid #eee;
        }
        .link-texto {
            font-size: 11px;
            color: #777;
            word-break: break-all;
        }

        @media print {
            body { background: #fff; padding: 0; }
            .topo, .aviso-localhost { display: none !important; }
            .card { box-shadow: none; border: 2px solid #000; }
        }
    </style>
</head>
<body>

    <div class="topo">
        <div>
            <label for="urlBase"><strong>URL Base do Servidor (IP na rede local):</strong></label><br>
            <input type="text" id="urlBase" value="<?= htmlspecialchars($url_base) ?>">
            <button class="btn" onclick="gerarTodos()">Atualizar Links</button>
        </div>
        <button class="btn btn-print" onclick="window.print()">🖨️ Imprimir Placas</button>

        <div class="aviso-localhost" id="avisoLocalhost">
            ⚠️ <strong>Atenção:</strong> Troque <code>localhost</code> pelo <strong>IPv4 do seu computador</strong> (ex: <code>192.168.0.15</code>) e clique em <strong>Atualizar Links</strong> antes de imprimir!
        </div>
    </div>

    <div class="grade">
        <?php if (empty($bancas)): ?>
            <p>Nenhuma banca cadastrada na tabela <strong>QRcodes</strong> ainda.</p>
        <?php else: ?>
            <?php foreach ($bancas as $index => $b): ?>
                <div class="card">
                    <h2><?= htmlspecialchars($b['nome_banca']) ?></h2>
                    <div class="valor">+<?= htmlspecialchars($b['valor']) ?> infor coins</div>
                    
                    <div class="qr-box" id="qr-<?= $index ?>" data-codigo="<?= htmlspecialchars($b['codigo']) ?>"></div>
                    
                    <div class="link-texto" id="texto-<?= $index ?>"></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

        <script>
        function gerarTodos() {
            const urlBase = document.getElementById('urlBase').value.trim();
            const aviso = document.getElementById('avisoLocalhost');
            
            if (urlBase.includes('localhost') || urlBase.includes('127.0.0.1')) {
                aviso.style.display = 'block';
            } else {
                aviso.style.display = 'none';
            }

            const boxes = document.querySelectorAll('.qr-box');

            boxes.forEach((box, index) => {
                const codigo = box.getAttribute('data-codigo');
                const linkCompleto = urlBase + encodeURIComponent(codigo);

                // Substitua a linha antiga do imgUrl por esta:
                const imgUrl = "https://quickchart.io" + encodeURIComponent(linkCompleto);

                
                box.innerHTML = `<img src="${imgUrl}" alt="QR Code ${codigo}" width="180" height="180">`;
                document.getElementById('texto-' + index).textContent = linkCompleto;
            });
        }

        window.onload = gerarTodos;
    </script>

</body>
</html>