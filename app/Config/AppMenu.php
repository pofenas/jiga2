<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppMenu extends BaseConfig
{
    public array $menu = [
        ['Inicio',      ''],
        ['Eventos',             'eventos/listar',   'Administrador'],
        ['Roles',               'roles/listar',     'Administrador'],
        ['Usuarios',            'usuarios/listar',  'Miembro Comité Organizador'],
        ['Items',               'items/listar',     'Miembro Comité Organizador'],
        ['Categorías',          'categorias/listar','Miembro Comité Organizador'],
        ['Plantillas',          'plantillas/listar','Miembro Comité Organizador'],
        ['Presentaciones',      'presentaciones/listar','Miembro Comité Organizador'],
        ['Validar usuarios',    'validacion/listar','Miembro Comité Organizador'],
        ['Calificar trabajos',  'calificaciones', 'Miembro Comité Científico'],
        ['Lista de premios',    'premios/listar', 'Miembro Comité Organizador','Administrador'],
        ['Adjudicación de premios','premios/adjudicar','Miembro Comité Organizador','Administrador']
    ];
}
