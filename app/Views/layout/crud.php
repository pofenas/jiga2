<?php
/**
 * Vista genérica CRUD
 *
 * Variables esperadas desde el controlador:
 *   string $titulo        Nombre de la entidad (ej: 'Eventos')
 *   array  $columnas      Columnas a mostrar: [['campo'=>'nombre', 'label'=>'Nombre'], ...]
 *   array  $filas         Array de objetos con los datos
 *   string $pk            Nombre del campo clave primaria (ej: 'id_evento')
 *   string $ruta_base     Ruta base del controlador (ej: 'eventos')
 */
?>
<?php if (session()->getFlashdata('exito')) : ?>
    <div class="alert alert-exito">
        <?= session()->getFlashdata('exito') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')) : ?>
    <div class="alert alert-error">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>


<div class="crud-container">

    <!-- ── Cabecera ── -->
    <div class="crud-header">
        <h2><?= esc($titulo) ?></h2>
        <button class="btn btn-nuevo" id="btn-nuevo">
            ＋ Nuevo
        </button>
    </div>

    <!-- ── Tabla de listado ── -->
    <div class="crud-tabla-wrapper">
        <?php if (empty($filas)) : ?>
            <p class="crud-vacio">No hay registros.</p>
        <?php else : ?>
            <table class="crud-tabla" id="crud-tabla">
    <thead>
        <tr>
            <th class="col-acciones"><?= !empty($acciones_extra) ? 'Acciones' : '' ?></th>
            <?php foreach ($columnas as $col) : ?>
                <?php if (strtolower(substr($col['campo'], 0, 2)) === 'id') : continue; endif; ?>
                <th><?= esc($col['label']) ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($filas as $fila) : ?>
            <tr class="crud-fila"
                data-id="<?= esc($fila->{$pk}) ?>"
                data-fila='<?= htmlspecialchars(json_encode($fila), ENT_QUOTES, 'UTF-8') ?>'>
                <td class="col-acciones">
                    <?php foreach ($acciones_extra ?? [] as $accion) : ?>
                        <a href="<?= esc($accion['ruta'] . '/' . $fila->{$pk}) ?>"
                        class="btn btn-accion-extra">
                            <?= $accion['label'] ?>
                        </a>
                    <?php endforeach; ?>
                    <button class="btn btn-borrar"
                            data-id="<?= esc($fila->{$pk}) ?>">
                        🗑 Borrar
                    </button>
                </td>
                <?php foreach ($columnas as $col) : ?>
                    <?php if (strtolower(substr($col['campo'], 0, 2)) === 'id') : continue; endif; ?>
                    <td>
                        <?php if (!empty($col_descarga) && $col['campo'] === $col_descarga) : ?>
                            <a href="<?= esc($ruta_descarga . '/' . $fila->{$pk}) ?>">
                                <?= esc($fila->{$col['campo']}) ?>
                            </a>
                        <?php else : ?>
                            <?= esc($fila->{$col['campo']}) ?>
                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

        <?php endif; ?>
    </div>

</div><!-- .crud-container -->


<!-- ══════════════════════════════════════
     MODAL DE EDICIÓN / CREACIÓN
══════════════════════════════════════ -->
<div id="crud-modal-overlay" class="modal-overlay" style="display:none;">
    <div class="modal-box">

        <div class="modal-header">
            <h3 id="modal-titulo">Nuevo registro</h3>
            <button class="modal-cerrar" id="modal-cerrar">✕</button>
        </div>

        <form id="crud-form" method="post" action="">
            <?= csrf_field() ?>
            <input type="hidden" name="<?= esc($pk) ?>" id="form-pk" value="">

            <div class="modal-body" id="modal-fields">
                <!-- Los campos se generan dinámicamente con JS -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar" id="btn-cancelar">Cancelar</button>
                <button type="submit" class="btn btn-guardar">Guardar</button>
            </div>
        </form>

    </div>
</div>


<!-- ══════════════════════════════════════
     ESTILOS
══════════════════════════════════════ -->
<style>
    .crud-container {
        font-family: Arial, sans-serif;
        font-size: 14px;
    }

    /* Cabecera */
    .crud-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .crud-header h2 {
        font-size: 20px;
        color: #2c3e50;
    }

    /* Botones */
    .btn {
        padding: 7px 14px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        transition: background 0.15s;
    }

    .btn-nuevo   { background: #27ae60; color: #fff; }
    .btn-nuevo:hover { background: #1e8449; }

    .btn-editar  { background: #2980b9; color: #fff; margin-right: 4px; }
    .btn-editar:hover { background: #1f6391; }

    .btn-borrar  { background: #e74c3c; color: #fff; }
    .btn-borrar:hover { background: #b03a2e; }

    .btn-guardar { background: #27ae60; color: #fff; }
    .btn-guardar:hover { background: #1e8449; }

    .btn-cancelar { background: #95a5a6; color: #fff; margin-right: 8px; }
    .btn-cancelar:hover { background: #717d7e; }

    /* Tabla */
    .crud-tabla-wrapper {
        overflow-x: auto;
    }

    .crud-tabla {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-radius: 6px;
        overflow: hidden;
    }

    .crud-tabla thead th {
        background: #2c3e50;
        color: #fff;
        padding: 10px 14px;
        text-align: left;
        font-size: 13px;
    }

    .crud-tabla tbody tr {
        border-bottom: 1px solid #ecf0f1;
        cursor: pointer;
        transition: background 0.1s;
    }

    .crud-tabla tbody tr:hover {
        background: #f0f4f8;
    }

    .crud-tabla tbody td {
        padding: 9px 14px;
    }

    .col-acciones { width: 220px; text-align: center; }


    .crud-vacio {
        color: #7f8c8d;
        font-style: italic;
        padding: 16px 0;
    }

    /* Modal overlay */
    .modal-overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-box {
        background: #fff;
        border-radius: 8px;
        width: 440px;
        max-width: 95vw;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        display: flex;
        flex-direction: column;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #ecf0f1;
    }

    .modal-header h3 {
        font-size: 16px;
        color: #2c3e50;
    }

    .modal-cerrar {
        background: none;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #7f8c8d;
    }

    .modal-cerrar:hover { color: #2c3e50; }

    .modal-body {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .modal-body label {
        font-size: 13px;
        color: #555;
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .modal-body input,
    .modal-body select,
    .modal-body textarea {
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        width: 100%;
    }

    .modal-body input:focus {
        outline: none;
        border-color: #2980b9;
        box-shadow: 0 0 0 2px rgba(41,128,185,0.15);
    }

    .modal-footer {
        padding: 14px 20px;
        border-top: 1px solid #ecf0f1;
        display: flex;
        justify-content: flex-end;
    }
    .alert {
    padding: 10px 16px;
    border-radius: 4px;
    margin-bottom: 16px;
    font-size: 14px;
    }

    .alert-exito { background: #d5f5e3; color: #1e8449; border: 1px solid #a9dfbf; }
    .alert-error { background: #fadbd8; color: #b03a2e; border: 1px solid #f1948a; }

    .btn-accion-extra       { background:#8e44ad; color:#fff; text-decoration:none;
                          display:inline-flex; align-items:center; margin-right:4px; }
    .btn-accion-extra:hover { background:#6c3483; }

</style>


<!-- ══════════════════════════════════════
     JAVASCRIPT
══════════════════════════════════════ -->
<script>
// Columnas y configuración pasadas desde PHP
const CRUD_COLUMNAS  = <?= json_encode($columnas) ?>;
const CRUD_PK        = <?= json_encode($pk) ?>;
const CRUD_RUTA_BASE = <?= json_encode($ruta_base) ?>;
const CRUD_CAMPOS_FORM = <?= json_encode($campos_form ?? null) ?>;
const CRUD_ENCTYPE      = <?= json_encode($enctype      ?? null) ?>;
const CRUD_COL_DESCARGA = <?= json_encode($col_descarga ?? null) ?>;
const CRUD_RUTA_DESCARGA= <?= json_encode($ruta_descarga ?? null) ?>;
const CRUD_ACCIONES_EXTRA = <?= json_encode($acciones_extra ?? []) ?>;




$(function ()
    {
    const overlay   = $('#crud-modal-overlay');
    const form      = $('#crud-form');
    const modalBody = $('#modal-fields');

    // ── Genera los campos del formulario a partir de $columnas ──
function generarCampos(datos)
    {
    modalBody.empty();

    // Si hay campos_form personalizados, los usamos; si no, generamos desde columnas
    const fuente = CRUD_CAMPOS_FORM ?? CRUD_COLUMNAS.map(c => ({
        campo : c.campo,
        label : c.label,
        tipo  : 'text',
    }));

    fuente.forEach(function (col)
        {
        if (col.campo.toLowerCase().startsWith('id') && !CRUD_CAMPOS_FORM) { return; }

        const valor = datos ? (datos[col.campo] ?? '') : (col.valor ?? '');

        if (col.tipo === 'hidden')
            {
            modalBody.append(
                `<input type="hidden" name="${col.campo}" value="${valor}">`
            );
            return;
            }

        if (col.tipo === 'select')
            {
            let options = col.opciones.map(function (opt)
                {
                const selected = String(opt[col.opt_val]) === String(valor) ? 'selected' : '';
                return `<option value="${opt[col.opt_val]}" ${selected}>${opt[col.opt_txt]}</option>`;
                }).join('');

            modalBody.append(`
                <label>
                    ${col.label}
                    <select name="${col.campo}" required>${options}</select>
                </label>`);
            return;
            }

        if (col.tipo === 'number')
            {
            const step = col.step ?? 'any';
            modalBody.append(`
                <label>
                    ${col.label}
                    <input type="number"
                        step="${step}"
                        name="${col.campo}"
                        value="${$('<div>').text(valor).html()}">
                </label>`);
            return;
            }
        if (col.tipo === 'file')
            {
            const accept = col.accept ?? '';
            const nota   = col.nota   ?? '';
            modalBody.append(`
                <label>
                    ${col.label}
                    ${nota ? `<small style="color:#888">${nota}</small>` : ''}
                    <input type="file"
                        name="${col.campo}"
                        accept="${accept}">
                </label>`);
            return;
            }
        if (col.tipo === 'checkbox')
            {
            const checked = valor == true || valor === 't' || valor === '1' ? 'checked' : '';
            modalBody.append(`
                <label style="flex-direction:row; align-items:center; gap:10px; cursor:pointer;">
                    <input type="checkbox"
                        name="${col.campo}"
                        value="1"
                        ${checked}
                        style="width:auto; margin:0;">
                    ${col.label}
                </label>`);
            return;
            }

        // Tipo text por defecto
        modalBody.append(`
            <label>
                ${col.label}
                <input type="text"
                       name="${col.campo}"
                       value="${$('<div>').text(valor).html()}">
            </label>`);
        });
    }


    // ── Abre el modal ──
    function abrirModal(titulo, datos, id)
        {
        $('#modal-titulo').text(titulo);
        $('#form-pk').val(id ?? '');
        generarCampos(datos);

        const accion = id
            ? `${CRUD_RUTA_BASE}/actualizar`
            : `${CRUD_RUTA_BASE}/crear`;

        form.attr('action', accion);

        // enctype para subida de archivos
        if (CRUD_ENCTYPE)
            {
            form.attr('enctype', CRUD_ENCTYPE);
            }
        else
            {
            form.removeAttr('enctype');
            }

        overlay.fadeIn(150);
        modalBody.find('input').first().focus();
    }


    // ── Cierra el modal ──
    function cerrarModal()
        {
        overlay.fadeOut(150);
        form[0].reset();
        }

    // ── Click en "Nuevo" ──
    $('#btn-nuevo').on('click', function ()
        {
        abrirModal('Nuevo registro', null, null);
        });

    // ── Click en fila → editar ──
    $('#crud-tabla').on('click', 'tr.crud-fila', function (e)
        {
        // Evitamos que el click en los botones de acción abra el modal dos veces
        if ($(e.target).closest('.btn-editar, .btn-borrar').length) { return; }

        const datos = JSON.parse($(this).attr('data-fila'));
        const id    = $(this).attr('data-id');
        abrirModal('Editar registro', datos, id);
        });

    // ── Click en botón "Editar" ──
    $('#crud-tabla').on('click', '.btn-editar', function (e)
        {
        e.stopPropagation();
        const datos = JSON.parse($(this).attr('data-fila'));
        const id    = $(this).attr('data-id');
        abrirModal('Editar registro', datos, id);
        });

    // ── Click en botón "Borrar" ──
    $('#crud-tabla').on('click', '.btn-borrar', function (e)
        {
        e.stopPropagation();
        const id = $(this).attr('data-id');

        if (!confirm('¿Confirmas que deseas borrar este registro?')) { return; }

        $.post(`${CRUD_RUTA_BASE}/borrar`, {
            [CRUD_PK]      : id,
            csrf_token_name: $('input[name="csrf_token_name"]').val()
            })
            .done(function ()
                {
                location.reload();
                })
            .fail(function ()
                {
                alert('Error al borrar el registro.');
                });
        });

    // ── Cerrar modal ──
    $('#modal-cerrar, #btn-cancelar').on('click', cerrarModal);

    overlay.on('click', function (e)
        {
        if ($(e.target).is(overlay)) { cerrarModal(); }
        });

    // ── Escape cierra el modal ──
    $(document).on('keydown', function (e)
        {
        if (e.key === 'Escape') { cerrarModal(); }
        });
    });
</script>
