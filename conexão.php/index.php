<?php
session_start();
require 'conectar.php';

// Restaura a sessão pelo cookie persistente caso o usuário tenha fechado o navegador
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['dbverse_uid'])) {
    $_SESSION['usuario_id'] = (int)$_COOKIE['dbverse_uid'];
}

// Se já estiver cadastrado, manda direto para o painel
if (isset($_SESSION['usuario_id'])) {
    header("Location: index1.php");
    exit;
}

// Quando clicar em cadastrar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty(trim($_POST['nome']))) {
    $nome = trim($_POST['nome']);
    
    $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, saldo) VALUES (?, 0)");
    $stmt->execute([$nome]);
    
    $novo_id = $pdo->lastInsertId();
    
    // Salva na sessão E em um cookie válido por 30 dias
    $_SESSION['usuario_id'] = $novo_id;
    setcookie('dbverse_uid', $novo_id, time() + (86400 * 30), "/");
    
    $redirect = !empty($_GET['voltar_codigo']) 
        ? "resgatar.php?codigo=" . urlencode($_GET['voltar_codigo']) 
        : "index1.php";
        
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Derocaçao.css">
    <link rel="preconnect" href="https://googleapis.com">
    <link rel="preconnect" href="https://gstatic.com" crossorigin>
    <link href="https://googleapis.com/css2?family=BBH+Bogle&family=Merriweather+Sans:ital,wght@0,300..800;1,300..800&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <title>Cadastro Informática</title>
</head>
<body>
    <nav>
        <form action="" method="POST">
            <h1>DBverse cadastro</h1><br>
            <?php if (isset($_GET['msg'])): ?>
                <p style="color: #721c24; background: #ffe8ec; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <?= htmlspecialchars($_GET['msg']) ?>
                </p>
            <?php endif; ?>
            <label>Digite seu nome para se cadastrar:</label>
            <input type="text" name="nome" required>
            <button type="submit">Enviar</button><br>
            <p>Será entregue um número de identificação único para você, junte <strong>infor coins</strong> escaneando os QR codes ao terminar cada apresentação de informática e compre brindes ao final!</p>
        </form>
    </nav>
</body>
</html>
