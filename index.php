<?php
require_once 'includes/config_session.php';
requireLogin();
require_once 'includes/db.php';

$totalDispositivos = (int)$pdo->query('SELECT COUNT(*) FROM devices')->fetchColumn();
$totalDiagnosticos = (int)$pdo->query('SELECT COUNT(*) FROM diagnostics')->fetchColumn();
$totalAlertas      = (int)$pdo->query("SELECT COUNT(*) FROM diagnostics WHERE estado_diagnostico = 'Alerta'")->fetchColumn();
$totalCriticos     = (int)$pdo->query("SELECT COUNT(*) FROM diagnostics WHERE estado_diagnostico = 'Crítico'")->fetchColumn();

$sql = "SELECT
            d.id,
            dev.nombre_dispositivo,
            dev.tipo_dispositivo,
            dev.ubicacion,
            dev.ip_address,
            d.parametro_revisado,
            d.lectura,
            d.estado_diagnostico,
            d.fecha_registro
        FROM diagnostics d
        INNER JOIN devices dev ON d.device_id = dev.id
        ORDER BY d.fecha_registro DESC";

$diagnosticos = $pdo->query($sql)->fetchAll();

$idRolActivo = (int)($_SESSION['id_rol'] ?? 0);

function pillClass(string $estado): string
{
    return match ($estado) {
        'Óptimo'  => 'pill pill-optimo',
        'Alerta'  => 'pill pill-alerta',
        'Crítico' => 'pill pill-critico',
        default   => 'pill',
    };
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema de Telemetría RA - UPT Aragua</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="app-header">
    <div class="app-header-brand">
        <div class="brand-logo">RA</div>
        <div>
            <h1>Sistema de Telemetría de Infraestructura</h1>
            <p>UPT Aragua — Diagnóstico en tiempo real vía Realidad Aumentada</p>
        </div>
    </div>

    <div class="app-header-user">
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
            <span class="user-role">Rol activo: <?= htmlspecialchars($_SESSION['rol_nombre']) ?> (ID_ROL: <?= $idRolActivo ?>)</span>
        </div>
        <a href="logout.php" class="btn btn-outline">Cerrar sesión</a>
    </div>
</header>

<main class="app-main">

    <section class="stats-grid">
        <div class="stat-card">
            <span class="stat-value"><?= $totalDispositivos ?></span>
            <span class="stat-label">Dispositivos monitoreados</span>
        </div>
        <div class="stat-card">
            <span class="stat-value"><?= $totalDiagnosticos ?></span>
            <span class="stat-label">Diagnósticos registrados</span>
        </div>
        <div class="stat-card stat-card-warning">
            <span class="stat-value"><?= $totalAlertas ?></span>
            <span class="stat-label">En estado de alerta</span>
        </div>
        <div class="stat-card stat-card-danger">
            <span class="stat-value"><?= $totalCriticos ?></span>
            <span class="stat-label">En estado crítico</span>
        </div>
    </section>

    <section class="panel">
        <div class="panel-header">
            <h2>Telemetría e Inventario de Diagnósticos</h2>
            <?php if (puedeEditar()): ?>
                <a href="crear.php" class="btn btn-primary">+ Registrar nuevo diagnóstico</a>
            <?php endif; ?>
        </div>

        <div class="table-wrapper">
            <table class="telemetry-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dispositivo</th>
                        <th>Tipo</th>
                        <th>Ubicación</th>
                        <th>IP</th>
                        <th>Parámetro</th>
                        <th>Lectura</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <?php if (puedeEditar() || puedeEliminar()): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($diagnosticos)): ?>
                        <tr>
                            <td colspan="10" class="empty-state">Aún no hay diagnósticos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($diagnosticos as $row): ?>
                            <tr>
                                <td><?= (int)$row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nombre_dispositivo']) ?></td>
                                <td><?= htmlspecialchars($row['tipo_dispositivo']) ?></td>
                                <td><?= htmlspecialchars($row['ubicacion']) ?></td>
                                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                                <td><?= htmlspecialchars($row['parametro_revisado']) ?></td>
                                <td><?= htmlspecialchars($row['lectura']) ?></td>
                                <td><span class="<?= pillClass($row['estado_diagnostico']) ?>"><?= htmlspecialchars($row['estado_diagnostico']) ?></span></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($row['fecha_registro']))) ?></td>

                                <?php if (puedeEditar() || puedeEliminar()): ?>
                                    <td class="actions-cell">
                                        <?php if (puedeEditar()): ?>
                                            <a href="editar.php?id=<?= (int)$row['id'] ?>" class="btn btn-sm btn-edit">Editar</a>
                                        <?php endif; ?>
                                        <?php if (puedeEliminar()): ?>
                                            <a href="eliminar.php?id=<?= (int)$row['id'] ?>"
                                               class="btn btn-sm btn-delete"
                                               onclick="return confirm('¿Eliminar este registro de diagnóstico? Esta acción no se puede deshacer.');">Eliminar</a>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<script src="js/script.js"></script>
</body>
</html>
