<?php if (session()->getFlashdata('exito')): ?>
    <div class="alert alert-exito">
        ✅ <?= esc(session()->getFlashdata('exito')) ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error">
        ❌ <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>
<?php if (!$es_presentador) : ?>
    <p class="text-muted">Selecciona una opción del menú.</p>
<?php else : ?>
    <!-- ══════════════════════════════════════
         PASTILLA 1 — Plantillas
    ══════════════════════════════════════ -->
    <div class="pastilla">
        <h3 class="pastilla-titulo">📄 Plantillas</h3>

        <?php if (empty($plantillas)) : ?>
            <p class="pastilla-vacio">No hay plantillas disponibles.</p>
        <?php else : ?>
            <div class="plantillas-grid">
                <?php foreach ($plantillas as $p) : ?>
                    <a href="<?= base_url('plantillas/descargar/' . $p->id_plantilla) ?>"
                       class="plantilla-card"
                       title="Descargar <?= esc($p->nombre) ?>">
                        <div class="plantilla-thumb">
                            <?= icono_extension($p->filename ?? '') ?>
                        </div>
                        <span class="plantilla-nombre"><?= esc($p->nombre) ?></span>
                        
                    </a>
                <?php endforeach ?>
            </div>
        <?php endif ?>
    </div>


   <!-- ══════════════════════════════════════
     PASTILLA 2 — Mis trabajos
══════════════════════════════════════ -->
<div class="pastilla">
    <h3 class="pastilla-titulo">📝 Mis trabajos</h3>

    <?php if (empty($trabajos)) : ?>
        <p class="pastilla-vacio">No has presentado ningún trabajo todavía.</p>
    <?php else : ?>
        <table class="crud-tabla" style="margin-bottom:16px">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>PDF</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trabajos as $t) : ?>
                    <tr>
                        <td><?= esc($t->nombre) ?></td>
                        <td><?= esc($t->nombre_categoria ?? '—') ?></td>
                        <td>
                            <?php if ($t->filename) : ?>
                                <a href="<?= base_url('trabajos/descargar/' . $t->id_trabajo) ?>">
                                    📕 <?= esc($t->filename) ?>
                                </a>
                            <?php else : ?>
                                <span class="pastilla-vacio">Sin archivo</span>
                            <?php endif ?>
                        </td>
                        <td style="display:flex;gap:6px">
                            <button class="btn btn-editar"
                                    <?= $t->tiene_calificacion ? 'disabled title="No se puede editar: ya tiene calificación"' : '' ?>
                                    onclick="<?= $t->tiene_calificacion ? '' : "abrirModalTrabajo({$t->id_trabajo}, '".esc($t->nombre,'js')."', {$t->id_categoria}, '".esc($t->lista_autores,'js')."')" ?>">
                                ✏ Editar
                            </button>
                            <form method="post" action="<?= base_url('trabajos/borrar') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id_trabajo" value="<?= $t->id_trabajo ?>">
                                <button class="btn btn-borrar"
                                        <?= $t->tiene_calificacion ? 'disabled title="No se puede borrar: ya tiene calificación"' : '' ?>
                                        <?= !$t->tiene_calificacion ? 'onclick="return confirm(\'¿Borrar este trabajo?\')"' : '' ?>>
                                    🗑 Borrar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <button class="btn btn-nuevo" onclick="abrirModalTrabajo()">+ Presentar trabajo</button>
</div>


<!-- Modal trabajo -->
<div id="modal-trabajo-overlay" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modal-trabajo-titulo">Nuevo trabajo</h3>
            <button class="modal-cerrar" id="modal-trabajo-cerrar">✕</button>
        </div>
        <form id="form-trabajo" method="post" action="" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="id_trabajo" id="trabajo-pk" value="">
            <div class="modal-body">
                <label>
                    Título
                    <input type="text" name="nombre" id="trabajo-nombre" required>
                </label>
                <label>
                    Categoría
                    <select name="id_categoria" id="trabajo-categoria" required>
                        <option value="">— Selecciona —</option>
                        <?php foreach ($categorias as $cat) : ?>
                            <option value="<?= $cat->id_categoria ?>">
                                <?= esc($cat->nombre) ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </label>
                <label>
                    Autores <small style="color:#888">(uno por línea)</small>
                    <textarea name="lista_autores" id="trabajo-autores"
                              rows="4" style="resize:vertical"></textarea>
                </label>
                <label>
                    Archivo PDF
                    <small style="color:#888" id="trabajo-pdf-nota"></small>
                    <input type="file" name="pdf" accept=".pdf">
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar"
                        id="modal-trabajo-cancelar">Cancelar</button>
                <button type="submit" class="btn btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalTrabajo(id, nombre, id_categoria, autores)
    {
    const esNuevo = !id;
    document.getElementById('modal-trabajo-titulo').textContent =
        esNuevo ? 'Nuevo trabajo' : 'Editar trabajo';
    document.getElementById('form-trabajo').action =
        esNuevo ? '<?= base_url('trabajos/crear') ?>'
                : '<?= base_url('trabajos/actualizar') ?>';
    document.getElementById('trabajo-pk').value         = id      ?? '';
    document.getElementById('trabajo-nombre').value     = nombre  ?? '';
    document.getElementById('trabajo-autores').value    = autores ?? '';
    document.getElementById('trabajo-pdf-nota').textContent =
        esNuevo ? '(requerido)' : '(dejar vacío para no reemplazar)';

    const sel = document.getElementById('trabajo-categoria');
    if (id_categoria) { sel.value = id_categoria; }

    document.getElementById('modal-trabajo-overlay').style.display = 'flex';
    }

document.getElementById('modal-trabajo-cerrar').onclick  =
document.getElementById('modal-trabajo-cancelar').onclick =
    () => document.getElementById('modal-trabajo-overlay').style.display = 'none';
</script>



    <!-- ══════════════════════════════════════
         PASTILLA 3 — Certificados
    ══════════════════════════════════════ -->
    <div class="pastilla">
        <h3 class="pastilla-titulo">🏅 Certificados</h3>
        <p class="pastilla-vacio">Aquí aparecerán tus certificados cuando estén disponibles.</p>
    </div>


</div>
<?php endif ?>



<?php
function icono_extension(string $filename): string
    {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $iconos = [
        'pdf'  => '📕',
        'doc'  => '📘',
        'docx' => '📘',
        'ppt'  => '📙',
        'pptx' => '📙',
        'xls'  => '📗',
        'xlsx' => '📗',
    ];
    $emoji = $iconos[$ext] ?? '📄';
    return '<span style="font-size:48px;line-height:1">' . $emoji . '</span>';
    }
?>



<style>
    .pastilla
        {
        background    : #fff;
        border-radius : 10px;
        box-shadow    : 0 2px 8px rgba(0,0,0,0.08);
        padding       : 24px;
        }

    .pastilla-titulo
        {
        font-size     : 16px;
        font-weight   : bold;
        color         : #2c3e50;
        margin-bottom : 16px;
        border-bottom : 2px solid #ecf0f1;
        padding-bottom: 8px;
        }

    .pastilla-vacio
        {
        color      : #95a5a6;
        font-style : italic;
        font-size  : 13px;
        }

    .plantillas-grid
        {
        display               : grid;
        grid-template-columns : repeat(auto-fill, minmax(120px, 1fr));
        gap                   : 16px;
        max-height            : 320px;
        overflow-y            : auto;
        padding-right         : 4px;
        }

    .plantilla-card
        {
        display         : flex;
        flex-direction  : column;
        align-items     : center;
        text-align      : center;
        padding         : 12px 8px;
        border-radius   : 8px;
        border          : 1px solid #ecf0f1;
        text-decoration : none;
        color           : #2c3e50;
        transition      : background 0.15s, box-shadow 0.15s;
        gap             : 6px;
        }

    .plantilla-card:hover
        {
        background : #f0f4f8;
        box-shadow : 0 2px 8px rgba(0,0,0,0.12);
        }

    .plantilla-nombre
        {
        font-size   : 13px;
        font-weight : bold;
        line-height : 1.2;
        }

    .plantilla-filename
        {
        font-size : 11px;
        color     : #95a5a6;
        }
    /* ── Botones generales ── */
.btn
    {
    display       : inline-flex;
    align-items   : center;
    gap           : 4px;
    padding       : 6px 14px;
    border        : none;
    border-radius : 6px;
    font-size     : 13px;
    font-weight   : 600;
    cursor        : pointer;
    transition    : background 0.15s, opacity 0.15s;
    }

    .btn:disabled
        {
        opacity : 0.4;
        cursor  : not-allowed;
        }

    /* Editar */
    .btn-editar
        {
        background : #5b9bd5;
        color      : #fff;
        }

    .btn-editar:hover:not(:disabled)
        {
        background : #3a7fc1;
        }

    /* Borrar */
    .btn-borrar
        {
        background : #7db8e8;
        color      : #fff;
        }

    .btn-borrar:hover:not(:disabled)
        {
        background : #5b9bd5;
        }

    /* Cancelar */
    .btn-cancelar
        {
        background : #a8c8e8;
        color      : #1a3a5c;
        }

    .btn-cancelar:hover
        {
        background : #7db8e8;
        }

    /* Guardar */
    .btn-guardar
        {
        background : #3a7fc1;
        color      : #fff;
        }

    .btn-guardar:hover
        {
        background : #2d6aa8;
        }

    /* Nuevo / Presentar trabajo */
    .btn-nuevo
        {
        background : #5b9bd5;
        color      : #fff;
        }

    .btn-nuevo:hover
        {
        background : #3a7fc1;
        }

</style>
