<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIGA 2 — Nueva contraseña</title>
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
            margin-bottom: 24px;
            color: #2c3e50;
            font-size: 22px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Cambiar contraseña</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif ?>

    <form method="post" action="<?= base_url('login/reset') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token) ?>">

        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password"
                   name="password"
                   class="form-control"
                   required
                   autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label">Repetir nueva contraseña</label>
            <input type="password"
                   name="password_confirm"
                   class="form-control"
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Guardar contraseña
        </button>

        <div class="text-center mt-3">
            <a href="<?= base_url('login') ?>">Volver al login</a>
        </div>
    </form>
</div>

</body>
</html>
