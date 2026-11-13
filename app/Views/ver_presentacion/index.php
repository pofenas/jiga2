<?php
/** @var object $presentacion */
/** @var array  $trabajos     */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= esc($presentacion->nombre) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body
        {
        margin: 0;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background: #f4f6f8;
        color: #222;
        }

    .vp-container
        {
        max-width: 900px;
        margin: 0 auto;
        padding: 12px;
        }

    .vp-title
        {
        font-size: 1.4rem;
        font-weight: 600;
        margin: 8px 0 16px;
        text-align: center;
        }

    .vp-accordion
        {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        overflow: hidden;
        margin-bottom: 10px;
        }

    .vp-header
        {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 14px;
        cursor: pointer;
        }

    .vp-header-text
        {
        font-size: 0.95rem;
        font-weight: 500;
        line-height: 1.3;
        flex: 1;
        margin-right: 8px;
        }

    .vp-header-text span
        {
        display: block;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        }

    .vp-chevron
        {
        font-size: 0.9rem;
        transition: transform .2s;
        }

    .vp-accordion.open .vp-chevron
        {
        transform: rotate(90deg);
        }

    .vp-body
        {
        max-height: 0;
        overflow: hidden;
        transition: max-height .25s ease;
        border-top: 1px solid #eef1f4;
        }

    .vp-body-inner
        {
        padding: 10px 14px 12px;
        font-size: 0.9rem;
        }

    .vp-autores
        {
        color: #1a73e8;
        text-decoration: underline;
        cursor: pointer;
        word-break: break-word;
        }

    /* Responsive */
    @media (min-width: 768px)
        {
        .vp-title
            {
            font-size: 1.6rem;
            text-align: left;
            }
        }
    </style>
</head>
<body>
<div class="vp-container">
    <div class="vp-title">
        <?= esc($presentacion->nombre) ?>
    </div>

    <?php if (empty($trabajos)): ?>
        <p>No hay trabajos en esta presentación.</p>
    <?php else: ?>
        <?php foreach ($trabajos as $t): ?>
            <?php
            $titulo_corto = mb_strlen($t->nombre) > 80
                ? mb_substr($t->nombre, 0, 77) . '…'
                : $t->nombre;
            ?>
            <div class="vp-accordion" data-id="<?= (int)$t->id_trabajo ?>">
                <div class="vp-header" onclick="toggleAccordion(this)">
                    <div class="vp-header-text">
                        <span><?= esc($titulo_corto) ?></span>
                    </div>
                    <div class="vp-chevron">▶</div>
                </div>
                <div class="vp-body">
                    <div class="vp-body-inner">
                        <div class="vp-autores"
                             onclick="abrirPdf(<?= (int)$t->id_trabajo ?>, event)">
                            <?= nl2br(esc($t->lista_autores)) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleAccordion(header)
    {
    const card = header.closest('.vp-accordion');
    const body = card.querySelector('.vp-body');
    const open = card.classList.contains('open');

    document.querySelectorAll('.vp-accordion').forEach(function (c)
        {
        c.classList.remove('open');
        const b = c.querySelector('.vp-body');
        if (b)
            {
            b.style.maxHeight = '0px';
            }
        });

    if (!open)
        {
        card.classList.add('open');
        body.style.maxHeight = body.scrollHeight + 'px';
        }
    }
    function abrirPdf(idTrabajo, ev)
            {
            if (ev)
                {
                ev.stopPropagation();
                }
            
            // *** RUTA PÚBLICA de VerPresentacion ***
            const urlCompleta = '<?= base_url('ver-presentacion/descargar-pdf/') ?>' + idTrabajo;
            window.open(urlCompleta, '_blank');
            }
    </script>
</body>
</html>