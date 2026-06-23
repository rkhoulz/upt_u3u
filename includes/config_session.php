<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

const ROL_ADMIN        = 1;
const ROL_DESARROLLADOR = 2;
const ROL_CONSULTA     = 3;

function requireLogin(): void
{
    if (empty($_SESSION['user_id'])) {
        echo "<script>
                alert('Acceso denegado. Debes iniciar sesión para ver esta página.');
                window.location.href = 'login.php';
              </script>";
        exit;
    }
}

function requireRole(array $allowedRoles): void
{
    $currentRole = (int)($_SESSION['id_rol'] ?? 0);

    if (!in_array($currentRole, $allowedRoles, true)) {
        echo "<script>
                alert('No tienes permisos suficientes para realizar esta acción.');
                window.location.href = 'index.php';
              </script>";
        exit;
    }
}

function esAdmin(): bool
{
    return (int)($_SESSION['id_rol'] ?? 0) === ROL_ADMIN;
}

function puedeEditar(): bool
{
    return in_array((int)($_SESSION['id_rol'] ?? 0), [ROL_ADMIN, ROL_DESARROLLADOR], true);
}

function puedeEliminar(): bool
{
    return esAdmin();
}
