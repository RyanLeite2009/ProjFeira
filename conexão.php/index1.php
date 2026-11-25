<?php
session_start();
require 'conectar.php';

// Restaura a sessão pelo cookie se o navegador foi fechado
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['dbverse_uid'])) {
    $_SESSION['usuario_id'] = (int)$_COOKIE['dbverse_uid'];
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, usuario, saldo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();

// Se o ID não existir mais no banco, limpa sessão e cookie
if (!$usuario) {
    session_destroy();
    setcookie('dbverse_uid', '', time() - 3600, "/");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Saldo - DBverse</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: #5c1623;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .retangulo {
            background-color: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 40px 30px;
            border: 5px solid gray;
            border-radius: 16px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            text-align: center;
        }
        .alerta {
            background-color: #ffe8ec;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: 600;
        }
        .nome-usuario {
            font-size: 18px;
            color: #444;
            margin-bottom: 18px;
        }
        .bloco-info { margin-bottom: 25px; }
        .rotulo {
            display: block;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #777777;
            margin-bottom: 4px;
        }
        .valor-id { font-size: 46px; font-weight: bold; color: #5c1623; }
        .valor-saldo { font-size: 46px; font-weight: bold; color: #2b2b2b; line-height: 1.1; }
        
        .btn-atualizar {
            width: 100%;
            background-color: #5c1623;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .btn-atualizar:hover { background-color: #7a1e2f; }
    </style>
</head>
<body>
    <div class="retangulo">
        <?php if (isset($_GET['msg'])): ?>
            <div class="alerta">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>

        <div class="nome-usuario">Olá, <strong><?= htmlspecialchars($usuario['usuario']) ?></strong>!</div>

        <div class="bloco-info">
            <span class="rotulo"><strong>Seu ID único</strong></span>
            <div class="valor-id">#<?= htmlspecialchars($usuario['id']) ?></div>
        </div>

        <div class="bloco-info">
            <span class="rotulo"><strong>Você possui</strong></span>
            <div class="valor-saldo"><?= htmlspecialchars($usuario['saldo']) ?><br><span style="font-size: 24px;">infor coins!</span></div>
        </div>

        <button class="btn-atualizar" onclick="window.location.href='index1.php'">🔄 Atualizar Saldo</button>
    </div>
</body>
</html>
