<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #27ae60;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
        .footer { font-size: 12px; color: #999; margin-top: 30px; }
    </style>
</head>
<body>
    <p>Hola <strong><?= esc($nombre) ?></strong>,</p>

    <p>Tu cuenta ha sido <strong>activada</strong>. Ya puedes acceder a la plataforma
    y presentar tu trabajo.</p>

    <a href="<?= esc($enlace) ?>" class="btn">Acceder a la plataforma</a>

    <p>O copia y pega este enlace en tu navegador:</p>
    <p><?= esc($enlace) ?></p>

    <div class="footer">JIGA 2 — <?= esc(base_url()) ?></div>
</body>
</html>
