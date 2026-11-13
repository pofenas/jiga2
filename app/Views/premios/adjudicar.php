<div class="adjudicacion">
    <h2><?= esc($titulo) ?></h2>

    <div class="columnas">
        <div class="columna premios">
            <h3>Premios</h3>

            <div id="lista-premios">
                <?php foreach ($premios as $premio): ?>
                    <div
                        class="tarjeta premio"
                        data-id="<?= $premio->id ?>"
                        data-premiado="<?= $premio->id_premiado ?? '' ?>"
                    >
                        <strong><?= esc($premio->nombre) ?></strong>
                        <small><?= esc($premio->nombre_categoria ?? 'Sin categoría') ?></small>

                        <div class="premiado-actual">
                            <?php if (!empty($premio->nombre_premiado)): ?>
                                🏆 <?= esc($premio->nombre_premiado) ?>
                            <?php else: ?>
                                Sin adjudicar
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="columna trabajos">
            <h3>Trabajos</h3>
            <div id="lista-trabajos">
                <p class="texto-suave">
                    Selecciona un premio para ver los trabajos que compiten.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
const csrf = '<?= csrf_hash() ?>';
let premioActual = null;
let trabajoSeleccionado = null;

document.querySelectorAll('.premio').forEach(function (tarjeta)
    {
    tarjeta.addEventListener('click', function ()
        {
        document.querySelectorAll('.premio').forEach(function (p)
            {
            p.classList.remove('activa');
            });

        tarjeta.classList.add('activa');
        premioActual = tarjeta.dataset.id;
        trabajoSeleccionado = tarjeta.dataset.premiado || null;

        cargarTrabajos(premioActual);
        });
    });

async function cargarTrabajos(idPremio)
    {
    const contenedor = document.getElementById('lista-trabajos');
    contenedor.innerHTML = '<p class="texto-suave">Cargando trabajos…</p>';

    const respuesta = await fetch('<?= base_url('premios/trabajos/') ?>' + idPremio);
    const datos = await respuesta.json();

    if (!datos.ok)
        {
        contenedor.innerHTML = '<p class="error">' + (datos.error ?? 'Error') + '</p>';
        return;
        }

    if (!datos.trabajos.length)
        {
        contenedor.innerHTML = '<p class="texto-suave">No hay trabajos admitidos en esta categoría.</p>';
        return;
        }

    contenedor.innerHTML = '';

    datos.trabajos.forEach(function (trabajo)
        {
        const tooltip = trabajo.detalles
            .map(function (d)
                {
                return d.item + ': ' + (d.nota === null ? 'Sin nota' : d.nota);
                })
            .join('\n');

        const tarjeta = document.createElement('div');
        tarjeta.className = 'tarjeta trabajo';
        tarjeta.dataset.id = trabajo.id_trabajo;
        tarjeta.title = tooltip;

        tarjeta.innerHTML =
            '<strong>' + trabajo.nombre + '</strong>' +
            '<small>' + (trabajo.lista_autores ?? '') + '</small>' +
            '<div class="nota">' +
            (trabajo.calificacion === null ? 'Sin calificación' : 'Nota: ' + trabajo.calificacion) +
            '</div>';

        tarjeta.addEventListener('click', function ()
            {
            adjudicar(trabajo.id_trabajo, tarjeta);
            });

        contenedor.appendChild(tarjeta);

        if (String(trabajo.id_trabajo) === String(trabajoSeleccionado))
            {
            tarjeta.classList.add('seleccionada');
            }
        });

    actualizarDisponibles();
    }

async function adjudicar(idTrabajo, tarjeta)
    {
    const liberar = String(idTrabajo) === String(trabajoSeleccionado);
    const idDestino = liberar ? null : idTrabajo;

    const cuerpo = new URLSearchParams();
    cuerpo.append('id_premio', premioActual);

    if (idDestino !== null)
        {
        cuerpo.append('id_trabajo', idDestino);
        }

    const respuesta = await fetch('<?= base_url('premios/adjudicar') ?>',
        {
        method: 'POST',
        headers:
            {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
            },
        body: cuerpo.toString() + '&csrf_test_name=' + encodeURIComponent(csrf)
        });

    const datos = await respuesta.json();

    if (!datos.ok)
        {
        alert(datos.error ?? 'No se pudo adjudicar el premio.');
        return;
        }

    trabajoSeleccionado = idDestino;

    const tarjetaPremio = document.querySelector('.premio[data-id="' + premioActual + '"]');
    tarjetaPremio.dataset.premiado = idDestino ?? '';

    tarjetaPremio.querySelector('.premiado-actual').innerHTML =
        idDestino
            ? '🏆 ' + tarjeta.querySelector('strong').textContent
            : 'Sin adjudicar';

    actualizarDisponibles();
    }

function actualizarDisponibles()
    {
    document.querySelectorAll('.trabajo').forEach(function (tarjeta)
        {
        const esSeleccionado =
            String(tarjeta.dataset.id) === String(trabajoSeleccionado);

        tarjeta.classList.toggle('seleccionada', esSeleccionado);
        tarjeta.classList.toggle('deshabilitada', trabajoSeleccionado !== null && !esSeleccionado);
        });
    }
</script>
