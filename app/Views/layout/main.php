<?php
$content_data = $content_data ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JIGA 2</title>

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/premios.css') ?>">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        #layout {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        #sidebar {
            width: 240px;
            min-width: 200px;
            background: #2c3e50;
            color: #ecf0f1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            padding: 12px 0;
        }

        #sidebar h2 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #95a5a6;
            padding: 0 16px 10px;
        }

        #nav-menu .ui-accordion-header {
            background: #34495e;
            color: #ecf0f1;
            border: none;
            border-radius: 0;
            padding: 10px 16px;
            font-size: 13px;
            cursor: pointer;
            outline: none;
        }

        #nav-menu .ui-accordion-header:hover,
        #nav-menu .ui-accordion-header-active {
            background: #1a252f;
            color: #ffffff;
        }

        #nav-menu .ui-accordion-header .ui-icon { filter: invert(1); }

        #nav-menu .ui-accordion-content {
            background: #253545;
            border: none;
            padding: 4px 0;
        }

        .menu-btn {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            color: #bdc3c7;
            padding: 8px 28px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }

        .menu-btn:hover       { background: #1a252f; color: #ffffff; }
        .menu-btn.active      { font-weight: bold; color: #ffffff; background: #1a252f; border-left: 3px solid #3498db; }

        .menu-link {
            display: block;
            width: 100%;
            text-align: left;
            background: transparent;
            border: none;
            color: #bdc3c7;
            padding: 10px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
            text-decoration: none;
        }

        .menu-link:hover  { background: #1a252f; color: #ffffff; }
        .menu-link.active { font-weight: bold; color: #ffffff; background: #1a252f; border-left: 3px solid #3498db; }

        /* Usuario en el pie del sidebar */
        #sidebar-user {
            margin-top: auto;
            padding: 12px 16px;
            border-top: 1px solid #34495e;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ecf0f1;
            font-size: 13px;
        }

        #sidebar-user a {
            color: #95a5a6;
            transition: color 0.15s;
        }

        #sidebar-user a:hover { color: #e74c3c; }

        #content {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #f4f6f8;
        }

        #sidebar .ui-accordion-header,
        #sidebar .ui-accordion-content { all: revert; }

        #sidebar a, #sidebar button { text-decoration: none; }

        #content { container-type: inline-size; }
    </style>
</head>
<body>

<div id="layout">

    <aside id="sidebar">
        <h2>Menú</h2>
        <nav id="nav-menu">
            <?php

            function menuGroupIsActive(array $group, string $active): bool
                {
                foreach ($group as $key => $item)
                    {
                    if ($key === 0) { continue; }
                    if (is_array($item))
                        {
                        if (menuGroupIsActive($item, $active)) { return true; }
                        }
                    elseif ($item === $active)
                        {
                        return true;
                        }
                    }
                return false;
                }

            function renderMenu(array $menu, string $active, $user, bool $root = true): array
                {
                $activeIndexes  = [];
                $accordionItems = [];

                foreach ($menu as $item)
                    {
                    if (!is_array($item)) { continue; }

                    // Filtro por rol
                    $rol = $item[2] ?? '';
                    if ($rol !== '' && !$user->tieneRol($rol)) { continue; }

                    $label       = $item[0] ?? '';
                    $hasChildren = false;
                    $children    = [];

                    for ($i = 1; $i < count($item); $i++)
                        {
                        if (is_array($item[$i]))
                            {
                            $hasChildren = true;
                            $children[]  = $item[$i];
                            }
                        }

                    if (!$hasChildren)
                        {
                        $controller  = $item[1] ?? '#';
                        $isActive    = ($controller === $active);
                        $activeClass = $isActive ? ' active' : '';
                        $url         = base_url($controller);

                        echo '<a href="' . esc($url) . '" class="menu-link' . $activeClass . '">'
                           . esc($label) . '</a>';
                        }
                    else
                        {
                        $groupActive      = menuGroupIsActive($item, $active);
                        $accordionItems[] = [
                            'label'       => $label,
                            'children'    => $children,
                            'groupActive' => $groupActive,
                        ];
                        }
                    }

                if (!empty($accordionItems))
                    {
                    $activeIdx = false;

                    echo '<div class="accordion-group">';

                    foreach ($accordionItems as $idx => $group)
                        {
                        if ($group['groupActive']) { $activeIdx = $idx; }

                        echo '<h3>' . esc($group['label']) . '</h3>';
                        echo '<div>';

                        foreach ($group['children'] as $child)
                            {
                            $childLabel      = $child[0] ?? '';
                            $childController = $child[1] ?? '#';
                            $isActive        = ($childController === $active);
                            $activeClass     = $isActive ? ' active' : '';
                            $url             = base_url($childController);

                            echo '<a href="' . esc($url) . '" class="menu-btn' . $activeClass . '">'
                               . esc($childLabel) . '</a>';
                            }

                        echo '</div>';
                        }

                    echo '</div>';

                    $activeIndexes[] = $activeIdx;
                    }

                return $activeIndexes;
                }

            $activeIndexes = renderMenu($menu, $active ?? '', $user);
            ?>
        </nav>

        <!-- Usuario + logout -->
        <div id="sidebar-user">
            <div style="display:flex; flex-direction:column; gap:2px; overflow:hidden;">
                <span style="font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    <?= esc($user->datos->nombre) ?>
                </span>
                <span style="font-size:11px; color:#95a5a6; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    <?= esc($user->datos->nombre_evento ?? '—') ?>
                </span>
            </div>
            <a href="<?= base_url('logout/logout') ?>" title="Cerrar sesión" style="flex-shrink:0; margin-left:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                    fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9
                            a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0
                            1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0
                            3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0
                            1.5-1.5v-2a.5.5 0 0 0-1 0v2z"/>
                    <path fill-rule="evenodd"
                        d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0
                            0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0
                            1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z"/>
                </svg>
            </a>
        </div>
    </aside>

    <main id="content">
        <?php if (!empty($content_view)) : ?>
            <?= view($content_view, $content_data) ?>
        <?php endif; ?>
    </main>

</div>

<script>
    const activeIndexes = <?= json_encode($activeIndexes ?? []) ?>;

    $(function ()
        {
        $('.accordion-group').each(function (groupIdx)
            {
            const activePanel = (activeIndexes[groupIdx] !== undefined)
                ? activeIndexes[groupIdx]
                : false;

            $(this).accordion(
                {
                collapsible : true,
                active      : activePanel,
                heightStyle : 'content',
                animate     : 180,
                });
            });
        });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
