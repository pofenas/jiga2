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

    <p>Gracias por registrarte en <strong><?= esc($nombre_evento) ?></strong>.<br>
    Haz clic en el botón para verificar tu dirección de email:</p>

    <a href="<?= esc($enlace) ?>" class="btn">Verificar mi cuenta</a>

    <p>O copia y pega este enlace en tu navegador:</p>
    <p><?= esc($enlace) ?></p>

    <p>Este enlace es de un solo uso. Si no te has registrado, ignora este mensaje.</p>

    <div class="footer">JIGA 2 — <?= esc(base_url()) ?></div>
</body>
</html>
