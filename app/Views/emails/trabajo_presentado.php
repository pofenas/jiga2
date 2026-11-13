<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
    table { border-collapse: collapse; width: 100%; margin: 16px 0; }
    td { padding: 8px 12px; border-bottom: 1px solid #ecf0f1; }
    td:first-child { font-weight: bold; color: #555; width: 160px; }
    .footer { font-size: 12px; color: #999; margin-top: 30px; }
</style>
</head>
<body>
    <p>Hola <strong><?= esc($nombre_usuario) ?></strong>,</p>
    <p>Tu trabajo ha sido registrado correctamente. Aquí tienes el resumen:</p>

    <table>
        <tr><td>Nº de registro</td><td>#<?= (int) $id_trabajo ?></td></tr>
        <tr><td>Fecha</td><td><?= esc($fecha) ?></td></tr>
        <tr><td>Título</td><td><?= esc($nombre_trabajo) ?></td></tr>
        <tr><td>Autores</td>
            <td><?= nl2br(esc($lista_autores)) ?></td></tr>
        <tr><td>Archivo PDF</td><td><?= esc($filename) ?></td></tr>
    </table>

    <p>Guarda este mensaje como justificante de presentación.</p>

    <div class="footer">JIGA 2 — <?= esc(base_url()) ?></div>
</body>
</html>
