<?php
/**
 * Variables disponibles:
 *   array $eventos  → array de objetos devuelto por EventosModel::listar()
 */
?>

<h2>Listado de eventos</h2>

<?php if (empty($eventos)) : ?>
    <p>No hay eventos registrados.</p>
<?php else : ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $evento) : ?>
                <tr>
                    <td><?= esc($evento->id_evento) ?></td>
                    <td><?= esc($evento->nombre) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
