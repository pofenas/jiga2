<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
    .footer { font-size: 12px; color: #999; margin-top: 30px; }
</style>
</head>
<body>
    <p>Hola <strong><?= esc($nombre_miembro) ?></strong>,</p>

    <p>Se ha presentado un nuevo trabajo que está pendiente de evaluación:</p>

    <p style="font-size:16px; font-weight:bold; color:#2c3e50">
        <?= esc($nombre_trabajo) ?>
    </p>

    <p>Accede a la plataforma para revisarlo.</p>

    <div class="footer">JIGA 2 — <?= esc(base_url()) ?></div>
</body>
</html>
