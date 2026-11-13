<?php

namespace App\Models;

use CodeIgniter\Model;

class User extends Model
{
    private ?int $id_usuario = null;
    public object  $datos;
    public array   $roles = [];

    public function __construct()
        {
        parent::__construct();

        $this->id_usuario = session()->get('id_usuario');

        if (!$this->id_usuario)
            {
            header('Location: ' . base_url('login'));
            exit();
            }

        // Datos del usuario
        $this->datos = $this->db
            ->table('usuarios u')
            ->select('u.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = u.id_evento', 'left')
            ->where('u.id_usuario', $this->id_usuario)
            ->get()
            ->getRow();

        // Roles del usuario
        $filas = $this->db
            ->table('rel_usuarios_roles ur')
            ->select('r.nombre')
            ->join('roles r', 'r.id_rol = ur.id_rol')
            ->where('ur.id_usuario', $this->id_usuario)
            ->get()
            ->getResult();

        $this->roles = array_column(
            array_map(fn($f) => (array)$f, $filas),
            'nombre'
        );
        }

    public function tieneRol(string|array $roles = []): bool
        {
        if (empty($roles))      // ¿Seguro? Creo que debe ser false.
            return true;

        if (in_array('Administrador', $this->roles))
            return true;            // los administradores siempre tienen acceso

        $roles = (array)$roles;
        foreach ($roles as $rol)
            {
            if (in_array($rol, $this->roles))
                return true;
            }
        return false;
        }


}
