<div class="container mt-4">
    <h1 class="mb-4"><?= esc($titulo) ?></h1>

    <?php if (empty($eventos)): ?>
        <div class="alert alert-warning">
            No estás inscrito en ningún evento.
        </div>
    <?php else: ?>
        <?php foreach ($eventos as $evento): ?>
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h2 class="h4 mb-0"><?= esc($evento->nombre) ?></h2>
                </div>

                <div class="card-body">
                    <div class="row align-items-center mb-4">
                        <div class="col-12 col-md-8">
                            <h3 class="h5">Presentación de trabajos</h3>

                            <a
                                href="<?= esc($evento->url_trabajos) ?>"
                                class="btn btn-primary"
                            >
                                Ir a la presentación de trabajos
                            </a>
                        </div>

                        <div class="col-12 col-md-4 text-center">
                            <img
                                src="<?= esc($evento->qr_trabajos) ?>"
                                alt="QR presentación de trabajos de <?= esc($evento->nombre) ?>"
                                class="img-fluid"
                                style="max-width: 180px;"
                            >
                        </div>
                    </div>

                    <h3 class="h5 mb-3">Presentaciones</h3>

                    <?php if (empty($evento->presentaciones)): ?>
                        <div class="alert alert-secondary">
                            No hay presentaciones para este evento.
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($evento->presentaciones as $presentacion): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                    <div class="card h-100">
                                        <div class="card-body text-center d-flex flex-column">
                                            <?php if (!empty($presentacion->url_logo)): ?>
                                                <img
                                                    src="<?= esc($presentacion->url_logo) ?>"
                                                    alt="Logo de <?= esc($presentacion->nombre) ?>"
                                                    class="img-fluid mb-2"
                                                    style="max-height: 80px; object-fit: contain;"
                                                >
                                            <?php endif; ?>

                                            <h6 class="card-title mb-2">
                                                <?= esc($presentacion->nombre) ?>
                                            </h6>

                                            <img
                                                src="<?= esc($presentacion->url_qr) ?>"
                                                alt="QR de <?= esc($presentacion->nombre) ?>"
                                                class="img-fluid mb-2"
                                            >

                                            <a
                                                href="<?= esc($presentacion->url_publica) ?>"
                                                class="btn btn-outline-primary btn-sm mt-auto"
                                                target="_blank"
                                                rel="noopener"
                                            >
                                                Ver presentación
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>