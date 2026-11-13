<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIGA 2 — Enlace no válido</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 430px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 18px;
            color: #2c3e50;
            font-size: 22px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Enlace no válido</h2>

    <div class="alert alert-danger">
        El enlace de recuperación no es válido o ha caducado.
    </div>

    <a href="<?= base_url('login/forgot') ?>" class="btn btn-primary w-100">
        Solicitar un nuevo enlace
    </a>

    <div class="mt-3">
        <a href="<?= base_url('login') ?>">Volver al login</a>
    </div>
</div>

</body>
</html>
