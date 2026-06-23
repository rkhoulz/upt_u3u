<?php

require_once 'includes/config_session.php';
require_once 'includes/db.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if (isset($_GET['registrado'])) {
    $success = 'Cuenta creada con éxito. Ya puedes iniciar sesión.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Debes completar el usuario y la contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT id, nombre, usuario, password_hash, rol_nombre, id_rol FROM users WHERE usuario = :usuario LIMIT 1');
        $stmt->execute(['usuario' => $usuario]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['user_id']    = $user['id'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['usuario']    = $user['usuario'];
            $_SESSION['rol_nombre'] = $user['rol_nombre'];
            $_SESSION['id_rol']     = (int)$user['id_rol'];

            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Sistema de Telemetría RA - UPT Aragua</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-body">

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-logo"></div>
                <h1>Sistema de Telemetría</h1>
                <p class="login-subtitle">Diagnóstico de Infraestructura Tecnológica<br>UPT Aragua</p>
            </div>

            <?php if ($success !== ''): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="usuario">Usuario</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ej: admin" autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Ingresar al sistema</button>
            </form>

            <div class="login-footer">
                <p>¿No tienes una cuenta? <a href="registro.php" class="link-strong">Regístrate aquí</a></p>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
