<?php if (session()->getFlashdata('exito')) : ?>
    <div class="alert alert-exito"><?= session()->getFlashdata('exito') ?></div>
<?php endif ?>
<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-error"><?= session()->getFlashdata('error') ?></div>
<?php endif ?>

<div class="crud-container">
    <div class="crud-header">
        <h2><?= esc($titulo) ?></h2>
    </div>

    <?php if (empty($usuarios)) : ?>
        <p class="crud-vacio">No hay usuarios pendientes de validación.</p>
    <?php else : ?>
        <div class="crud-tabla-wrapper">
            <table class="crud-tabla">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Evento</th>
                        <th>Correo verificado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u) : ?>
                        <tr>
                            <td><?= esc($u->nombre) ?></td>
                            <td><?= esc($u->email) ?></td>
                            <td><?= esc($u->nombre_evento ?? '—') ?></td>
                            <td><?= $u->correo_verificado ? '✅' : '⏳ Pendiente' ?></td>
                            <td>
                                <form method="post"
                                      action="<?= base_url('validacion/validar') ?>"
                                      class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_usuario"
                                           value="<?= esc($u->id_usuario) ?>">
                                    <button class="btn btn-guardar"
                                            <?= !$u->correo_verificado ? 'disabled title="El usuario aún no ha verificado su correo"' : '' ?>>
                                        ✔ Validar
                                    </button>
                                </form>
                                <form method="post"
                                      action="<?= base_url('validacion/eliminar') ?>"
                                      class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_usuario"
                                           value="<?= esc($u->id_usuario) ?>">
                                    <button class="btn btn-borrar"
                                            onclick="return confirm('¿Eliminar este usuario?')">
                                        🗑 Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</div>
