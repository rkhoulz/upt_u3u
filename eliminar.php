<?php

require_once 'includes/config_session.php';
requireLogin();
requireRole([ROL_ADMIN]); // Únicamente id_rol === 1 puede continuar.
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

if ($id === null || !ctype_digit((string)$id)) {
    echo "<script>alert('Identificador de diagnóstico inválido.'); window.location.href='index.php';</script>";
    exit;
}
$id = (int)$id;

$stmt = $pdo->prepare('DELETE FROM diagnostics WHERE id = :id');
$stmt->execute(['id' => $id]);

header('Location: index.php');
exit;
