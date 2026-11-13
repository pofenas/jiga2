<?php
/** @var object[] $trabajos */
/** @var object[] $items   */
?>
<?php if (isset($_GET['exito'])): ?>
    <div class="alert alert-ok">✔ Calificación guardada correctamente.</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-error">✖ Ha ocurrido un error. Inténtalo de nuevo.</div>
<?php endif; ?>

<h2 style="margin-bottom:16px;">Calificación de trabajos</h2>

<table class="cal-tabla">
    <thead>
        <tr>
            <th>Trabajo</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>Admitido</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($trabajos as $t): ?>
        <tr class="cal-fila"
            data-id="<?= $t->id_trabajo ?>"
            data-nombre="<?= esc($t->nombre) ?>"
            data-items="<?= esc(json_encode($t->items)) ?>">
            <td>
                <a href="<?= base_url('calificaciones/descargar/' . $t->id_trabajo) ?>"
                   class="pdf-link" title="Descargar PDF">
                    📄 <?= esc($t->nombre) ?>
                </a>
            </td>
            <td><?= esc($t->categoria) ?></td>
            <td>
                <?php if ($t->estado === 'completo'): ?>
                    <span class="badge badge-ok">✔ Calificado</span>
                <?php elseif ($t->estado === 'parcial'): ?>
                    <span class="badge badge-parcial">◑ Parcial</span>
                <?php else: ?>
                    <span class="badge badge-pending">⏳ Pendiente</span>
                <?php endif; ?>
            </td>
            <td class="col-admitido">
                <input
                    type="checkbox"
                    class="chk-admitido"
                    data-id="<?= $t->id_trabajo ?>"
                    <?= $t->admitido ? 'checked' : '' ?>
                    title="<?= $t->admitido ? 'Admitido' : 'No admitido' ?>"
                >
            </td>

            <td>
                <button class="btn btn-calificar"
                        onclick="abrirModal(this.closest('tr'))">
                    ✏ Calificar
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- ══════════ MODAL ══════════ -->
<div id="modal-overlay" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3 id="modal-titulo"></h3>
        <form method="post" action="<?= base_url('calificaciones/guardar') ?>" id="modal-form">
            <?= csrf_field() ?>
            <input type="hidden" name="id_trabajo" id="modal-id-trabajo">

            <table class="modal-tabla">
                <thead>
                    <tr><th>Ítem</th><th style="width:100px;">Nota (0-10)</th></tr>
                </thead>
                <tbody id="modal-items">
                    <!-- se rellena por JS -->
                </tbody>
            </table>

            <div class="modal-botones">
                <button type="button" class="btn btn-cancelar"
                        onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn btn-guardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
 function abrirModal(fila)
    {
    const id    = fila.dataset.id;
    const nombre = fila.dataset.nombre;
    const items = JSON.parse(fila.dataset.items);

    document.getElementById('modal-titulo').textContent = nombre;
    document.getElementById('modal-id-trabajo').value   = id;

    const tbody = document.getElementById('modal-items');
    tbody.innerHTML = '';
    items.forEach(item =>
        {
        const nota = item.nota !== null ? item.nota : '';
        tbody.innerHTML +=
            `<tr>
                <td>${item.nombre}</td>
                <td><input type="number" name="notas[${item.id_item}]"
                        value="${nota}" min="0" max="10" step="0.5"
                        class="input-nota"></td>
            </tr>`;
        });

    document.getElementById('modal-overlay').style.display = 'flex';
    }

 function cerrarModal()
    {
    document.getElementById('modal-overlay').style.display = 'none';
    }

    // Cerrar al clicar fuera del modal
    document.getElementById('modal-overlay').addEventListener('click', function(e)
        {
        if (e.target === this) cerrarModal();
        });
</script>
<script>
    document.querySelectorAll('.chk-admitido').forEach(function(chk)
  {
    chk.addEventListener('change', function()
    {
        const idtrabajo = this.dataset.id;
        const checkbox  = this;

        fetch('<?= base_url('calificaciones/toggle-admitido') ?>', {
            method:  'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded',
                      'X-Requested-With': 'XMLHttpRequest'},
            body:    '<?= csrf_token() ?>=' + '<?= csrf_hash() ?>' +
                     '&idtrabajo=' + idtrabajo
        })
        .then(r => r.json())
        .then(function(data)
        {
            if (!data.ok)
            {
                // Revertir el estado si hubo error
                checkbox.checked = !checkbox.checked;
                alert('Error al cambiar el estado.');
            }
            else
            {
                checkbox.title = data.admitido ? 'Admitido' : 'No admitido';
            }
        })
        .catch(function()
        {
            checkbox.checked = !checkbox.checked;
            alert('Error de red.');
        });
    });
});
</script>


<style>
.cal-tabla          { width:100%; border-collapse:collapse; font-size:14px; }
.cal-tabla th       { background:#2c3e50; color:#fff; padding:8px 12px; text-align:left; }
.cal-tabla td       { padding:8px 12px; border-bottom:1px solid #e0e0e0; vertical-align:middle; }
.cal-tabla tr:hover td { background:#f0f4f8; }

.pdf-link           { color:#2980b9; text-decoration:none; font-weight:600; }
.pdf-link:hover     { text-decoration:underline; }

.badge              { padding:3px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.badge-ok           { background:#d5f5e3; color:#1e8449; }
.badge-pending      { background:#fef9e7; color:#b7950b; }

.btn-calificar      { background:#5b9bd5; color:#fff; border:none; border-radius:6px;
                      padding:5px 12px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-calificar:hover { background:#3a7fc1; }

/* Modal */
.modal-overlay      { position:fixed; inset:0; background:rgba(0,0,0,0.5);
                      display:flex; align-items:center; justify-content:center; z-index:1000; }
.modal-box          { background:#fff; border-radius:10px; padding:28px 32px;
                      width:480px; max-width:95vw; box-shadow:0 8px 32px rgba(0,0,0,0.2); }
.modal-box h3       { margin:0 0 18px; font-size:16px; color:#2c3e50; }

.modal-tabla        { width:100%; border-collapse:collapse; margin-bottom:20px; }
.modal-tabla th     { background:#f4f6f8; padding:7px 10px; text-align:left;
                      font-size:13px; border-bottom:2px solid #ddd; }
.modal-tabla td     { padding:7px 10px; border-bottom:1px solid #eee; font-size:13px; }

.input-nota         { width:70px; padding:4px 6px; border:1px solid #bbb;
                      border-radius:5px; font-size:13px; text-align:center; }

.modal-botones      { display:flex; justify-content:flex-end; gap:10px; }

.alert-ok           { background:#d5f5e3; color:#1e8449; padding:10px 16px;
                      border-radius:6px; margin-bottom:16px; font-weight:600; }
.alert-error        { background:#fadbd8; color:#c0392b; padding:10px 16px;
                      border-radius:6px; margin-bottom:16px; font-weight:600; }
.badge-parcial      { background:#d6eaf8; color:#1a5276; }

/* Igualar botones modal al botón Calificar */
.btn-guardar        { background:#5b9bd5; color:#fff; border:none; border-radius:6px;
                      padding:6px 16px; font-size:13px; font-weight:600; cursor:pointer; }
.btn-guardar:hover  { background:#3a7fc1; }

.btn-cancelar       { background:#5b9bd5; color:#fff; border:none; border-radius:6px;
                      padding:6px 16px; font-size:13px; font-weight:600; cursor:pointer;
                      opacity:0.6; }
.btn-cancelar:hover { opacity:1; background:#3a7fc1; }

</style>
