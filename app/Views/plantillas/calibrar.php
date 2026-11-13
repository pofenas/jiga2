<?php /** @var object $plantilla */ ?>

<div style="display:flex; gap:24px; align-items:flex-start;">

    <!-- ── Panel izquierdo: PDF con overlay ── -->
    <div style="flex:1; position:relative;">
        <h2 style="margin-bottom:12px;">
            Calibrar: <?= esc($plantilla->nombre) ?>
        </h2>

        <p style="font-size:13px; color:#555; margin-bottom:10px;">
            Haz click sobre el PDF para colocar el <strong>Título</strong>
            y los <strong>Autores</strong>. Las coordenadas se actualizan
            automáticamente en el panel derecho.
        </p>

        <div id="pdf-wrapper" style="position:relative; display:inline-block; border:2px solid #2c3e50; cursor:crosshair;">
            <canvas id="pdf-canvas"></canvas>

            <!-- Marcadores visuales -->
            <div id="marker-titulo"  class="marker marker-titulo"  style="display:none;">T</div>
            <div id="marker-autores" class="marker marker-autores" style="display:none;">A</div>

            <!-- Overlay cuadrícula -->
            <canvas id="grid-canvas" style="position:absolute; top:0; left:0; pointer-events:none;"></canvas>
        </div>
    </div>

    <!-- ── Panel derecho: coordenadas ── -->
    <div style="width:280px;">
        <form method="post" action="<?= base_url('plantillas/calibrar') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id_plantilla" value="<?= $plantilla->id_plantilla ?>">

            <div class="cal-grupo">
                <h3 class="cal-seccion titulo-color">📌 Título</h3>
                <label>X
                    <input type="number" step="0.1" name="pos_x_titulo"
                           id="pos_x_titulo" value="<?= $plantilla->pos_x_titulo ?? 100 ?>">
                </label>
                <label>Y
                    <input type="number" step="0.1" name="pos_y_titulo"
                           id="pos_y_titulo" value="<?= $plantilla->pos_y_titulo ?? 300 ?>">
                </label>
            </div>

            <div class="cal-grupo">
                <h3 class="cal-seccion autores-color">📌 Autores</h3>
                <label>X
                    <input type="number" step="0.1" name="pos_x_autores"
                           id="pos_x_autores" value="<?= $plantilla->pos_x_autores ?? 100 ?>">
                </label>
                <label>Y
                    <input type="number" step="0.1" name="pos_y_autores"
                           id="pos_y_autores" value="<?= $plantilla->pos_y_autores ?? 400 ?>">
                </label>
            </div>

            <div class="cal-grupo">
                <h3 class="cal-seccion">🔤 Tamaño de letra</h3>
                <label>pt
                    <input type="number" name="font_size" id="font_size"
                           value="<?= $plantilla->font_size ?? 16 ?>" min="6" max="72">
                </label>
            </div>

            <!-- Selector de marcador activo -->
            <div class="cal-grupo" style="margin-bottom:16px;">
                <p style="font-size:12px; color:#555; margin-bottom:6px;">
                    Click en el PDF coloca:
                </p>
                <label style="flex-direction:row; gap:8px; align-items:center;">
                    <input type="radio" name="marcador_activo" value="titulo" checked>
                    <span class="titulo-color">Título</span>
                </label>
                <label style="flex-direction:row; gap:8px; align-items:center;">
                    <input type="radio" name="marcador_activo" value="autores">
                    <span class="autores-color">Autores</span>
                </label>
            </div>

            <button type="submit" class="btn btn-guardar" style="width:100%;">
                💾 Guardar coordenadas
            </button>
        </form>

        <div style="margin-top:16px; font-size:12px; color:#888;">
            <strong>Nota:</strong> las coordenadas son en puntos PDF
            (origen arriba-izquierda). Una página A4 mide 595 × 842 pt.
        </div>
    </div>

</div>

<!-- pdf.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc =
    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

const PDF_URL    = '<?= base_url('plantillas/descargar/' . $plantilla->id_plantilla) ?>';
let   pdfPageW   = 595;  // puntos PDF (A4)
let   pdfPageH   = 842;
let   canvasW    = 0;
let   canvasH    = 0;

// Cargar y renderizar PDF
pdfjsLib.getDocument(PDF_URL).promise.then(function(pdf)
    {
    return pdf.getPage(1);
    })
    .then(function(page)
        {
        const viewport = page.getViewport({ scale: 1.5 });
        const canvas   = document.getElementById('pdf-canvas');
        const ctx      = canvas.getContext('2d');

        canvas.width  = viewport.width;
        canvas.height = viewport.height;
        canvasW = viewport.width;
        canvasH = viewport.height;

        // Dimensiones reales del PDF en puntos
        pdfPageW = page.getViewport({ scale: 1 }).width;
        pdfPageH = page.getViewport({ scale: 1 }).height;

        page.render({ canvasContext: ctx, viewport: viewport });

        dibujarCuadricula();
        colocarMarcadoresIniciales();
        });

function dibujarCuadricula()
    {
    const grid   = document.getElementById('grid-canvas');
    grid.width   = canvasW;
    grid.height  = canvasH;
    const ctx    = grid.getContext('2d');
    const paso   = 50; // puntos PDF entre líneas

    ctx.strokeStyle = 'rgba(100,149,237,0.25)';
    ctx.lineWidth   = 0.5;
    ctx.font        = '9px Arial';
    ctx.fillStyle   = 'rgba(100,149,237,0.6)';

    // Líneas verticales
    for (let x = 0; x <= pdfPageW; x += paso)
        {
        const cx = pdfToCanvas(x, 0).cx;
        ctx.beginPath();
        ctx.moveTo(cx, 0);
        ctx.lineTo(cx, canvasH);
        ctx.stroke();
        if (x > 0) ctx.fillText(x, cx + 2, 10);
        }

    // Líneas horizontales
    for (let y = 0; y <= pdfPageH; y += paso)
        {
        const cy = pdfToCanvas(0, y).cy;
        ctx.beginPath();
        ctx.moveTo(0, cy);
        ctx.lineTo(canvasW, cy);
        ctx.stroke();
        if (y > 0) ctx.fillText(y, 2, cy - 2);
        }
    }

// Convierte coordenadas PDF → canvas
function pdfToCanvas(px, py)
    {
    return {
        cx: (px / pdfPageW) * canvasW,
        cy: (py / pdfPageH) * canvasH,
    };
    }

// Convierte coordenadas canvas → PDF
function canvasToPdf(cx, cy)
    {
    return {
        px: (cx / canvasW) * pdfPageW,
        py: (cy / canvasH) * pdfPageH,
    };
    }

function colocarMarcadoresIniciales()
    {
    moverMarcador('titulo',
        parseFloat(document.getElementById('pos_x_titulo').value),
        parseFloat(document.getElementById('pos_y_titulo').value));

    moverMarcador('autores',
        parseFloat(document.getElementById('pos_x_autores').value),
        parseFloat(document.getElementById('pos_y_autores').value));
    }

function moverMarcador(tipo, px, py)
    {
    const wrapper = document.getElementById('pdf-wrapper');
    const marker  = document.getElementById('marker-' + tipo);
    const rect    = wrapper.getBoundingClientRect();
    const pos     = pdfToCanvas(px, py);

    marker.style.left    = pos.cx + 'px';
    marker.style.top     = pos.cy + 'px';
    marker.style.display = 'block';
    }

// Click en el PDF
document.getElementById('pdf-canvas').addEventListener('click', function(e)
    {
    const rect   = this.getBoundingClientRect();
    const cx     = e.clientX - rect.left;
    const cy     = e.clientY - rect.top;
    const {px, py} = canvasToPdf(cx, cy);
    const tipo   = document.querySelector('input[name="marcador_activo"]:checked').value;

    if (tipo === 'titulo')
        {
        document.getElementById('pos_x_titulo').value = px.toFixed(1);
        document.getElementById('pos_y_titulo').value = py.toFixed(1);
        }
    else
        {
        document.getElementById('pos_x_autores').value = px.toFixed(1);
        document.getElementById('pos_y_autores').value = py.toFixed(1);
        }

    moverMarcador(tipo, px, py);
    });
</script>

<style>
.cal-grupo      { background:#f8f9fa; border-radius:8px; padding:14px;
                  margin-bottom:14px; display:flex; flex-direction:column; gap:8px; }
.cal-seccion    { font-size:13px; font-weight:700; margin:0 0 4px; color:#2c3e50; }
.cal-grupo label { font-size:12px; color:#555; display:flex;
                   flex-direction:column; gap:3px; }
.cal-grupo input[type=number] { padding:5px 8px; border:1px solid #ccc;
                                 border-radius:4px; font-size:13px; }
.titulo-color   { color:#2980b9; }
.autores-color  { color:#27ae60; }

.marker         { position:absolute; width:22px; height:22px; border-radius:50%;
                  display:flex; align-items:center; justify-content:center;
                  font-size:11px; font-weight:700; color:#fff;
                  transform:translate(-50%, -50%);
                  pointer-events:none; box-shadow:0 2px 6px rgba(0,0,0,0.3); }
.marker-titulo  { background:#2980b9; }
.marker-autores { background:#27ae60; }
</style>
