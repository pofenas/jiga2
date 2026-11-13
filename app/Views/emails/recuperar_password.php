<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif;">
    <div style="max-width:600px; margin:40px auto; background:#ffffff; border:1px solid #ddd; border-radius:8px; overflow:hidden;">
        <div style="background:#2c3e50; color:#ffffff; padding:20px; text-align:center;">
            <h2 style="margin:0;">JIGA 2</h2>
        </div>

        <div style="padding:30px; color:#333;">
            <p>Hola <?= esc($nombre) ?>,</p>

            <p>
                Hemos recibido una solicitud para cambiar la contraseña de tu cuenta.
            </p>

            <p>
                Para establecer una nueva contraseña, pulsa en el siguiente enlace:
            </p>

            <p style="text-align:center; margin:30px 0;">
                <a href="<?= esc($enlace) ?>"
                   style="background:#0d6efd; color:#fff; text-decoration:none; padding:12px 22px; border-radius:6px; display:inline-block;">
                    Cambiar contraseña
                </a>
            </p>

            <p>
                Si no has solicitado este cambio, puedes ignorar este correo.
            </p>

            <p>
                Un saludo,<br>
                JIGA 2
            </p>
        </div>
    </div>
</body>
</html>
