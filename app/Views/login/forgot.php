<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIGA 2 — Recuperar contraseña</title>
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
            max-width: 420px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 16px;
            color: #2c3e50;
            font-size: 22px;
        }

        .help-text {
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Recuperar contraseña</h2>
    <p class="help-text">
        Introduce tu correo electrónico y te enviaremos un enlace para cambiar la contraseña.
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif ?>

    <form method="post" action="<?= base_url('login/forgot') ?>">
        <?= csrf_field() ?>

        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email"
                   name="email"
                   class="form-control"
                   required
                   autofocus>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Enviar enlace
        </button>

        <div class="text-center mt-3">
            <a href="<?= base_url('login') ?>">Volver al login</a>
        </div>
    </form>
</div>

</body>
</html>
