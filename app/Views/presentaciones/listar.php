<?php
$csrfName  = csrf_token();
$csrfHash  = csrf_hash();
?>

<div class="content-header">
    <h2><?= esc($titulo) ?></h2>
    <button class="btn btn-primary" onclick="abrirModalNuevo()">+ Nueva presentación</button>
</div>

<?php if (session()->getFlashdata('exito')): ?>
    <div class="alert alert-ok"><?= esc(session()->getFlashdata('exito')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<table class="tabla-crud">
    <thead>
        <tr>
            
            <th>Nombre</th>
            <th>Logo</th>
            <th>Acciones</th>
            <th style="width: 80px;">QR</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($presentaciones as $p): ?>
        <tr
            
            data-nombre="<?= esc($p->nombre) ?>"
            data-logo="<?= esc($p->logo ?? '') ?>"
        >
           

            <td><?= esc($p->nombre) ?></td>

            <td class="col-logo">
                <?php if (!empty($p->logo)): ?>
                    <img
                        src="<?= base_url('assets/img/' . $p->logo) ?>"
                        alt="Logo de <?= esc($p->nombre) ?>"
                        style="max-width:80px; max-height:80px;"
                    >
                <?php else: ?>
                    <span class="texto-suave">Sin logo</span>
                <?php endif; ?>
            </td>

            <td>
                <button
                    class="btn btn-sm btn-editar"
                    onclick="abrirModalEditar(this.closest('tr'))"
                >
                    ✏️ Editar
                </button>

                <button
                    class="btn btn-sm btn-definir"
                    onclick="abrirModalDefinir(<?= $p->id_presentacion ?>, '<?= esc($p->nombre, 'js') ?>')"
                >
                    📋 Definir
                </button>

                <form
                    method="post"
                    action="<?= base_url('presentaciones/borrar') ?>"
                    style="display:inline"
                    onsubmit="return confirm('¿Borrar esta presentación?')"
                >
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_presentacion" value="<?= $p->id_presentacion ?>">
                    <button type="submit" class="btn btn-sm btn-borrar">🗑️ Borrar</button>
                </form>
            </td>

            <td>
                <a
                    href="<?= base_url('presentaciones/qr/' . $p->id_presentacion) ?>"
                    class="btn btn-sm btn-primary"
                    target="_blank"
                    title="Generar código QR"
                >
                    <i class="fas fa-qrcode"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- ===================== MODAL NUEVA/EDITAR ===================== -->
<div id="modal-overlay" class="modal-overlay" style="display:none">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modal-titulo">Presentación</h3>
            <button class="btn-cerrar" onclick="cerrarModal()">✕</button>
        </div>

        <form
            id="modal-form"
            method="post"
            enctype="multipart/form-data"
            action="<?= base_url('presentaciones/actualizar') ?>"
        >
            <?= csrf_field() ?>
            <input type="hidden" name="id_presentacion" id="modal-id">

            <div class="modal-body">
                <label>
                    Nombre
                    <input
                        type="text"
                        name="nombre"
                        id="modal-nombre"
                        required
                        maxlength="256"
                        class="form-control"
                    >
                </label>

                <label>
                    Logo del QR
                    <input
                        type="file"
                        name="logo"
                        id="modal-logo"
                        accept=".png,.jpg,.jpeg,.webp"
                    >
                </label>

                <div id="modal-logo-actual" class="logo-actual"></div>
            </div>

            <div class="modal-botones">
                <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">
                    Cancelar
                </button>

                <button type="submit" class="btn btn-guardar">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================== MODAL DEFINIR ===================== -->
<div id="modal-definir-overlay" class="modal-overlay" style="display:none">
    <div class="modal-box modal-box-lg">
        <div class="modal-header">
            <h3 id="modal-definir-titulo">Definir presentación</h3>
            <button class="btn-cerrar" onclick="cerrarModalDefinir()">✕</button>
        </div>
        <div class="modal-body">
            <div id="modal-definir-cargando">Cargando trabajos…</div>
            <table id="tabla-trabajos" class="tabla-crud" style="display:none">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="check-todos" title="Marcar/desmarcar todos"> Todos</th>
                        <th>Trabajo</th>
                        <th>Autores</th>
                        <th>Orden</th>
                    </tr>
                </thead>
                <tbody id="tbody-trabajos"></tbody>
            </table>
        </div>
        <div class="modal-botones">
            <button type="button" class="btn btn-cancelar" onclick="cerrarModalDefinir()">Cancelar</button>
            <button type="button" class="btn btn-guardar" onclick="guardarDefinicion()">💾 Guardar</button>
        </div>
    </div>
</div>

<script>
const BASE_URL        = '<?= base_url() ?>';
const CSRF_NAME       = '<?= $csrfName ?>';
let   csrfHash        = '<?= $csrfHash ?>';
let   idPresentacionActual = null;

// ── Modal nueva/editar ──────────────────────────────────────────────
function abrirModalNuevo()
    {
    document.getElementById('modal-titulo').textContent = 'Nueva presentación';
    document.getElementById('modal-id').value           = '';
    document.getElementById('modal-nombre').value       = '';
    document.getElementById('modal-form').action        = BASE_URL + 'presentaciones/crear';
    document.getElementById('modal-overlay').style.display = 'flex';
    }

function abrirModalEditar(fila)
    {
    const id = fila.dataset.id;
    const nombre = fila.dataset.nombre;
    const logo = fila.dataset.logo;

    document.getElementById('modal-id').value = id;
    document.getElementById('modal-nombre').value = nombre;
    document.getElementById('modal-logo').value = '';

    const contenedor = document.getElementById('modal-logo-actual');

    if (logo)
        {
        contenedor.innerHTML =
            '<p>Logo actual:</p>' +
            '<img src="' + '<?= base_url('assets/img/') ?>' + logo + '" ' +
            'alt="Logo actual" style="max-width:120px;">';
        }
    else
        {
        contenedor.innerHTML = '<p>Esta presentación todavía no tiene logo.</p>';
        }

    document.getElementById('modal-overlay').style.display = 'flex';
    }

function cerrarModal()
    {
    document.getElementById('modal-overlay').style.display = 'none';
    }

// ── Modal definir ───────────────────────────────────────────────────
function abrirModalDefinir(idPresentacion, nombre)
    {
    idPresentacionActual = idPresentacion;
    document.getElementById('modal-definir-titulo').textContent = 'Definir: ' + nombre;
    document.getElementById('modal-definir-cargando').style.display = 'block';
    document.getElementById('tabla-trabajos').style.display          = 'none';
    document.getElementById('modal-definir-overlay').style.display   = 'flex';

    fetch(BASE_URL + 'presentaciones/trabajos/' + idPresentacion)
        .then(r => r.json())
        .then(trabajos =>
            {
            renderizarTrabajos(trabajos);
            document.getElementById('modal-definir-cargando').style.display = 'none';
            document.getElementById('tabla-trabajos').style.display          = 'table';
            })
        .catch(() =>
            {
            document.getElementById('modal-definir-cargando').textContent = 'Error al cargar trabajos.';
            });
    }

function cerrarModalDefinir()
    {
    document.getElementById('modal-definir-overlay').style.display = 'none';
    }

function renderizarTrabajos(trabajos)
    {
    const tbody = document.getElementById('tbody-trabajos');
    tbody.innerHTML = '';

    trabajos.forEach((t, idx) =>
        {
        const tr = document.createElement('tr');
        tr.dataset.idTrabajo = t.id_trabajo;

        const checked = t.incluido ? 'checked' : '';

        tr.innerHTML = `
            <td><input type="checkbox" class="chk-trabajo" ${checked} onchange="onCheckChange(this)"></td>
            <td>${escHtml(t.nombre)}</td>
            <td>${escHtml(t.lista_autores)}</td>
            <td>
                <button type="button" class="btn btn-sm" onclick="moverFila(this, -1)" ${!t.incluido ? 'disabled' : ''}>▲</button>
                <button type="button" class="btn btn-sm" onclick="moverFila(this, 1)"  ${!t.incluido ? 'disabled' : ''}>▼</button>
            </td>
        `;
        tbody.appendChild(tr);
        });

    document.getElementById('check-todos').checked = trabajos.every(t => t.incluido);
    document.getElementById('check-todos').addEventListener('change', function()
        {
        document.querySelectorAll('.chk-trabajo').forEach(chk =>
            {
            chk.checked = this.checked;
            onCheckChange(chk);
            });
        reordenarFilas();
        });
    }

function onCheckChange(chk)
    {
    const tr      = chk.closest('tr');
    const botones = tr.querySelectorAll('button');
    botones.forEach(b => b.disabled = !chk.checked);
    reordenarFilas();
    }

function reordenarFilas()
    {
    const tbody     = document.getElementById('tbody-trabajos');
    const filas     = Array.from(tbody.querySelectorAll('tr'));
    const incluidas = filas.filter(tr => tr.querySelector('.chk-trabajo').checked);
    const excluidas = filas.filter(tr => !tr.querySelector('.chk-trabajo').checked);

    [...incluidas, ...excluidas].forEach(tr => tbody.appendChild(tr));
    }

function moverFila(btn, direccion)
    {
    const tbody     = document.getElementById('tbody-trabajos');
    const tr        = btn.closest('tr');
    const incluidas = Array.from(tbody.querySelectorAll('tr'))
                          .filter(f => f.querySelector('.chk-trabajo').checked);
    const idx       = incluidas.indexOf(tr);
    const nuevoIdx  = idx + direccion;

    if (nuevoIdx < 0 || nuevoIdx >= incluidas.length)
        {
        return;
        }

    const referencia = incluidas[nuevoIdx];
    if (direccion === -1)
        {
        tbody.insertBefore(tr, referencia);
        }
    else
        {
        referencia.after(tr);
        }
    }

function guardarDefinicion()
    {
    const filas = Array.from(
        document.querySelectorAll('#tbody-trabajos tr')
    ).filter(tr => tr.querySelector('.chk-trabajo').checked);

    const ids = filas.map(tr => tr.dataset.idTrabajo);

    const body = new URLSearchParams();
    body.append(CSRF_NAME, csrfHash);
    body.append('id_presentacion', idPresentacionActual);
    ids.forEach(id => body.append('trabajos[]', id));

    fetch(BASE_URL + 'presentaciones/guardar-trabajos', {
        method : 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body   : body.toString(),
    })
    .then(r => r.json())
    .then(data =>
        {
        if (data.ok)
            {
            cerrarModalDefinir();
            // Actualizar CSRF para siguiente petición
            if (data.csrf) { csrfHash = data.csrf; }
            }
        else
            {
            alert('Error al guardar.');
            }
        })
    .catch(() => alert('Error de red.'));
    }

function escHtml(str)
    {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }
</script>