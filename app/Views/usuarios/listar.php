<h2><?= esc($titulo) ?></h2>

<!-- Botón Nuevo usuario -->
<button class="btn btn-primary mb-3"
        data-bs-toggle="modal"
        data-bs-target="#modalNuevoUsuario">
    + Nuevo usuario
</button>

<!-- Tabla principal de usuarios -->
<table class="table table-bordered table-hover align-middle">
    <thead class="table-dark">
       <tr>
            <th>ID</th>
            <th>Login</th>
            <th>Nombre</th>
            <th>Evento</th>   <!-- ← añadir -->
            <th>Roles</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($usuarios as $u): ?>
        <tr>
            <td><?= esc($u->id_usuario) ?></td>
            <td><?= esc($u->login) ?></td>
            <td><?= esc($u->nombre) ?></td>
            <td><?= esc($u->nombre_evento ?? '—') ?></td>

            <!-- Tabla anidada de roles -->
            <td>
                <table class="table table-sm table-striped mb-1">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($u->roles as $rol): ?>
                        <tr>
                            <td><?= esc($rol->nombre) ?></td>
                            <td>
                                <form method="post"
                                      action="<?= base_url('usuarios/quitar-rol') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_usuario"
                                           value="<?= $u->id_usuario ?>">
                                    <input type="hidden" name="id_rol"
                                           value="<?= $rol->id_rol ?>">
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Quitar este rol?')">
                                        Borrar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>

                <!-- Botón Nuevo rol para este usuario -->
                <button class="btn btn-success btn-sm"
                        onclick="abrirModalRol(<?= $u->id_usuario ?>, '<?= esc($u->login) ?>')">
                    + Rol
                </button>
            </td>

            <!-- Acciones del usuario -->
            <td>
                <button class="btn btn-warning btn-sm"
                        onclick="abrirModalEditar(
                            <?= $u->id_usuario ?>,
                            '<?= esc($u->login,  'js') ?>',
                            '<?= esc($u->nombre, 'js') ?>',
                            <?= (int) $u->id_evento ?>
                        )">
                    Editar
                </button>
                <form method="post"
                      action="<?= base_url('usuarios/borrar') ?>"
                      class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_usuario"
                           value="<?= $u->id_usuario ?>">
                    <button class="btn btn-danger btn-sm"
                            onclick="return confirm('¿Borrar usuario?')">
                        Borrar
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>


<!-- ============================================================ -->
<!-- Modal: Nuevo usuario                                          -->
<!-- ============================================================ -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('usuarios/crear') ?>">
            <?= csrf_field() ?>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo usuario</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Login</label>
                        <input type="text" name="login"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= esc($usuario->email ?? '') ?>"
                        >
                    </div>
                </div>
               <?php if ($es_admin) : ?>
                    <div class="mb-3">
                        <label class="form-label">Evento</label>
                        <select name="id_evento" class="form-select" required>
                            <option value="">— Selecciona evento —</option>
                            <?php foreach ($eventos as $e) : ?>
                                <option value="<?= $e->id_evento ?>">
                                    <?= esc($e->nombre) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                <?php endif ?>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ============================================================ -->
<!-- Modal: Editar usuario                                         -->
<!-- ============================================================ -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('usuarios/actualizar') ?>">
            <?= csrf_field() ?>
            <input type="hidden" id="edit_id_usuario" name="id_usuario">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar usuario</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Login</label>
                        <input type="text" id="edit_login" name="login"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="edit_nombre" name="nombre"
                               class="form-control" required>
                    </div>
                     <div class="mb-3">
                        <label class="form-label">Password
                            <small class="text-muted">(dejar vacío para no cambiar)</small>
                        </label>
                        <input type="password" id="edit_password" name="password"
                            class="form-control" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            name="email"
                            value="<?= esc($usuario->email ?? '') ?>"
                        >
                    </div>
                </div>
                <?php if ($es_admin) : ?>
                    <div class="mb-3">
                        <label class="form-label">Evento</label>
                        <select id="edit_id_evento" name="id_evento" class="form-select" required>
                            <option value="">— Selecciona evento —</option>
                            <?php foreach ($eventos as $e) : ?>
                                <option value="<?= $e->id_evento ?>">
                                    <?= esc($e->nombre) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                <?php endif ?>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ============================================================ -->
<!-- Modal: Asignar rol                                            -->
<!-- ============================================================ -->
<div class="modal fade" id="modalAsignarRol" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= base_url('usuarios/asignar-rol') ?>">
            <?= csrf_field() ?>
            <input type="hidden" id="rol_id_usuario" name="id_usuario">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar rol a
                        <span id="rol_login_label"></span>
                    </h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Rol disponible</label>
                    <select name="id_rol" id="selectRoles"
                            class="form-select" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Asignar</button>
                </div>
            </div>
        </form>
    </div>
</div>


<!-- ============================================================ -->
<!-- Scripts                                                       -->
<!-- ============================================================ -->
<script>
function abrirModalEditar(id, login, nombre, id_evento)
    {
    document.getElementById('edit_id_usuario').value = id;
    document.getElementById('edit_login').value      = login;
    document.getElementById('edit_nombre').value     = nombre;
    document.getElementById('edit_password').value   = '';

    const sel = document.getElementById('edit_id_evento');
    if (sel) { sel.value = id_evento; }

    new bootstrap.Modal(document.getElementById('modalEditarUsuario')).show();
    }
function abrirModalRol(id_usuario, login)
    {
    document.getElementById('rol_id_usuario').value     = id_usuario;
    document.getElementById('rol_login_label').textContent = login;

    const select = document.getElementById('selectRoles');
    select.innerHTML = '<option value="">Cargando...</option>';

    fetch(`<?= base_url('usuarios/roles-disponibles') ?>/${id_usuario}`)
        .then(r => r.json())
        .then(roles => {
            if (roles.length === 0)
                {
                select.innerHTML =
                    '<option value="">No hay roles disponibles</option>';
                return;
                }
            select.innerHTML = roles.map(r =>
                `<option value="${r.id_rol}">${r.nombre}</option>`
            ).join('');
        });

    new bootstrap.Modal(document.getElementById('modalAsignarRol')).show();
    }
</script>
