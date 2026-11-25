<?php
session_start();
require 'conectar.php';

// Restaura a sessão pelo cookie se o usuário já tinha se cadastrado antes
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['dbverse_uid'])) {
    $_SESSION['usuario_id'] = (int)$_COOKIE['dbverse_uid'];
}

$codigo = trim($_GET['codigo'] ?? '');

// Se não estiver logado, manda para o cadastro (index.php) guardando o código
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index1.php?voltar_codigo=" . urlencode($codigo) . "&msg=" . urlencode("Cadastre seu nome primeiro para resgatar!"));
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

if (empty($codigo)) {
    header("Location: index1.php?msg=" . urlencode("Nenhum código informado!"));
    exit;
}

// 1. Busca o QR Code no banco
$stmt = $pdo->prepare("SELECT banca, nome_banca, valor FROM QRcodes WHERE codigo = ?");
$stmt->execute([$codigo]);
$qrcode = $stmt->fetch();

if (!$qrcode) {
    header("Location: index1.php?msg=" . urlencode("QR Code inválido!"));
    exit;
}

// 2. Verifica se o usuário já resgatou essa banca
$stmt = $pdo->prepare("SELECT 1 FROM usuarios_has_QRcodes WHERE usuarios_id = ? AND QRcodes_banca = ?");
$stmt->execute([$usuario_id, $qrcode['banca']]);

if ($stmt->fetch()) {
    header("Location: index1.php?msg=" . urlencode("Você já resgatou as moedas da " . $qrcode['nome_banca'] . "!"));
    exit;
}

// 3. Registra o resgate e soma o saldo com transação
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO usuarios_has_QRcodes (usuarios_id, QRcodes_banca) VALUES (?, ?)");
    $stmt->execute([$usuario_id, $qrcode['banca']]);

    $stmt = $pdo->prepare("UPDATE usuarios SET saldo = saldo + ? WHERE id = ?");
    $stmt->execute([$qrcode['valor'], $usuario_id]);

    $pdo->commit();
    header("Location: index1.php?msg=" . urlencode("Parabéns! +" . $qrcode['valor'] . " moedas da " . $qrcode['nome_banca'] . "!"));
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: index1.php?msg=" . urlencode("Erro ao processar resgate."));
    exit;
}
