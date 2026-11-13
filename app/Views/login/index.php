<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIGA 2 — Login</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-box {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 28px;
            color: #2c3e50;
            font-size: 22px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>JIGA 2</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
    <?php endif ?>

    <form method="post" action="<?= base_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="login"
                   class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password"
                   class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
            Entrar
        </button>
        <div class="text-center mt-3">
            <a href="<?= base_url('login/forgot') ?>">¿Olvidaste tu contraseña?</a>
        </div>

    </form>
</div>

</body>
</html>
