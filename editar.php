<?php

require_once 'includes/config_session.php';
requireLogin();
requireRole([ROL_ADMIN, ROL_DESARROLLADOR]);
require_once 'includes/db.php';

$id = $_GET['id'] ?? null;

if ($id === null || !ctype_digit((string)$id)) {
    echo "<script>alert('Identificador de diagnóstico inválido.'); window.location.href='index.php';</script>";
    exit;
}
$id = (int)$id;

$stmt = $pdo->prepare(
    'SELECT id, device_id, parametro_revisado, lectura, estado_diagnostico FROM diagnostics WHERE id = :id LIMIT 1'
);
$stmt->execute(['id' => $id]);
$diagnostic = $stmt->fetch();

if (!$diagnostic) {
    echo "<script>alert('El diagnóstico solicitado no existe.'); window.location.href='index.php';</script>";
    exit;
}

$devices = $pdo->query('SELECT id, nombre_dispositivo, tipo_dispositivo, ubicacion FROM devices ORDER BY nombre_dispositivo')->fetchAll();

$parametros = ['Temperatura', 'Tráfico de Datos', 'Estado Operativo'];
$estados    = ['Óptimo', 'Alerta', 'Crítico'];

$errors = [];
$form = [
    'device_id'          => $diagnostic['device_id'],
    'parametro_revisado' => $diagnostic['parametro_revisado'],
    'lectura'             => $diagnostic['lectura'],
    'estado_diagnostico' => $diagnostic['estado_diagnostico'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['device_id']          = $_POST['device_id'] ?? '';
    $form['parametro_revisado'] = $_POST['parametro_revisado'] ?? '';
    $form['lectura']             = trim($_POST['lectura'] ?? '');
    $form['estado_diagnostico'] = $_POST['estado_diagnostico'] ?? '';

    if ($form['device_id'] === '' || !ctype_digit((string)$form['device_id'])) {
        $errors[] = 'Debes seleccionar un dispositivo válido.';
    }
    if (!in_array($form['parametro_revisado'], $parametros, true)) {
        $errors[] = 'Debes seleccionar un parámetro válido.';
    }
    if ($form['lectura'] === '') {
        $errors[] = 'La lectura no puede estar vacía.';
    }
    if (!in_array($form['estado_diagnostico'], $estados, true)) {
        $errors[] = 'Debes seleccionar un estado de diagnóstico válido.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare(
            'UPDATE diagnostics
             SET device_id = :device_id,
                 parametro_revisado = :parametro_revisado,
                 lectura = :lectura,
                 estado_diagnostico = :estado_diagnostico
             WHERE id = :id'
        );
        $update->execute([
            'device_id'          => (int)$form['device_id'],
            'parametro_revisado' => $form['parametro_revisado'],
            'lectura'            => $form['lectura'],
            'estado_diagnostico' => $form['estado_diagnostico'],
            'id'                 => $id,
        ]);

        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Diagnóstico | Sistema de Telemetría RA</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="app-header">
    <div class="app-header-brand">
        <div class="brand-logo">RA</div>
        <div>
            <h1>Sistema de Telemetría de Infraestructura</h1>
            <p>Proyecto de Grado · UPT Aragua</p>
        </div>
    </div>
    <div class="app-header-user">
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="user-role">Rol activo: <?= htmlspecialchars($_SESSION['rol_nombre']) ?> (ID_ROL: <?= (int)$_SESSION['id_rol'] ?>)</span>
        </div>
        <a href="logout.php" class="btn btn-outline">Cerrar sesión</a>
    </div>
</header>

<main class="app-main app-main-narrow">

    <section class="panel">
        <div class="panel-header">
            <h2>Editar diagnóstico #<?= $id ?></h2>
            <a href="index.php" class="btn btn-outline">← Volver al dashboard</a>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="editar.php?id=<?= $id ?>" class="data-form" novalidate>
            <div class="form-group">
                <label for="device_id">Dispositivo</label>
                <select id="device_id" name="device_id" required>
                    <?php foreach ($devices as $dev): ?>
                        <option value="<?= (int)$dev['id'] ?>" <?= (string)$dev['id'] === (string)$form['device_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dev['nombre_dispositivo']) ?> — <?= htmlspecialchars($dev['tipo_dispositivo']) ?> (<?= htmlspecialchars($dev['ubicacion']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="parametro_revisado">Parámetro revisado</label>
                    <select id="parametro_revisado" name="parametro_revisado" required>
                        <?php foreach ($parametros as $p): ?>
                            <option value="<?= htmlspecialchars($p) ?>" <?= $p === $form['parametro_revisado'] ? 'selected' : '' ?>><?= htmlspecialchars($p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="lectura">Lectura</label>
                    <input type="text" id="lectura" name="lectura" value="<?= htmlspecialchars($form['lectura']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="estado_diagnostico">Estado del diagnóstico</label>
                <select id="estado_diagnostico" name="estado_diagnostico" required>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?= htmlspecialchars($e) ?>" <?= $e === $form['estado_diagnostico'] ? 'selected' : '' ?>><?= htmlspecialchars($e) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
        </form>
    </section>

</main>

<script src="js/script.js"></script>
</body>
</html>
