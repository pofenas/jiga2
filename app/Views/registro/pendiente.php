<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Registro</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width:480px">
    <div class="alert alert-success">
        <h4>¡Registro completado!</h4>
        <p>Hemos enviado un enlace de verificación a <strong><?= esc($email) ?></strong>.</p>
        <p>Por favor revisa tu bandeja de entrada y haz clic en el enlace para activar tu cuenta.</p>
    </div>
</div>
</body></html>
