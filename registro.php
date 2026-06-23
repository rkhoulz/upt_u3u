<?php

require_once 'includes/config_session.php';
require_once 'includes/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors = [];
$form = [
    'nombre'  => '',
    'usuario' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['nombre']        = trim($_POST['nombre'] ?? '');
    $form['usuario']       = trim($_POST['usuario'] ?? '');
    $password              = $_POST['password'] ?? '';
    $passwordConfirm       = $_POST['password_confirm'] ?? '';

    if ($form['nombre'] === '') {
        $errors[] = 'Debes indicar tu nombre completo.';
    }

    if ($form['usuario'] === '') {
        $errors[] = 'Debes elegir un nombre de usuario.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{4,50}$/', $form['usuario'])) {
        $errors[] = 'El usuario debe tener entre 4 y 50 caracteres (letras, números, "_" o ".").';
    }

    if ($password === '' || $passwordConfirm === '') {
        $errors[] = 'Debes escribir y confirmar la contraseña.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $passwordConfirm) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    if (empty($errors)) {

        $check = $pdo->prepare('SELECT id FROM users WHERE usuario = :usuario LIMIT 1');
        $check->execute(['usuario' => $form['usuario']]);

        if ($check->fetch()) {
            $errors[] = 'Ese nombre de usuario ya está en uso. Elige otro.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $insert = $pdo->prepare(
                'INSERT INTO users (nombre, usuario, password_hash, rol_nombre, id_rol)
                 VALUES (:nombre, :usuario, :password_hash, :rol_nombre, :id_rol)'
            );
            $insert->execute([
                'nombre'        => $form['nombre'],
                'usuario'       => $form['usuario'],
                'password_hash' => $hash,
                'rol_nombre'    => 'Usuario Común',
                'id_rol'        => ROL_CONSULTA,
            ]);

            header('Location: login.php?registrado=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta | Sistema de Telemetría RA - UPT Aragua</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-logo">RA</div>
                <h1>Crear cuenta</h1>
                <p class="login-subtitle">Sistema de Telemetría · Diagnóstico de Infraestructura Tecnológica<br>Proyecto de Grado · UPT Aragua</p>
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

            <form method="POST" action="registro.php" class="login-form" id="registroForm" novalidate>
                <div class="form-group">
                    <label for="nombre">Nombre completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Juan Pérez" autocomplete="name" value="<?= htmlspecialchars($form['nombre']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ej: jperez" autocomplete="username" value="<?= htmlspecialchars($form['usuario']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" autocomplete="new-password" required>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" placeholder="Repite la contraseña" autocomplete="new-password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Crear cuenta</button>
            </form>

            <div class="login-footer">
                <p>¿Ya tienes una cuenta? <a href="login.php" class="link-strong">Inicia sesión</a></p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
