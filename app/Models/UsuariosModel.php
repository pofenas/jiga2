<?php

namespace App\Models;

class UsuariosModel extends CrudModel
{
    protected $table         = 'usuarios';
    protected $primaryKey    = 'id_usuario';
    protected $allowedFields = [
                                    'login',
                                    'password',
                                    'nombre',
                                    'email',
                                    'id_evento',
                                    'validado',
                                    'correo_verificado',
                                    'token_verificacion',
                                    'reset_token',
                                    'reset_token_expira',
                                ];


    public function listar(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('usuarios u')
            ->select('u.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = u.id_evento', 'left')
            ->orderBy('u.id_usuario', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('u.id_evento', $id_evento);
            }

        $usuarios = $builder->get()->getResultObject();

        foreach ($usuarios as $usuario)
            {
            $usuario->roles = $this->db
                ->table('rel_usuarios_roles r')
                ->select('r.id_rol, ro.nombre')
                ->join('roles ro', 'ro.id_rol = r.id_rol')
                ->where('r.id_usuario', $usuario->id_usuario)
                ->orderBy('ro.nombre', 'ASC')
                ->get()
                ->getResultObject();
            }

        return $usuarios;
        }

    public function eventosDisponibles(): array
        {
        return $this->db
            ->table('eventos')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultObject();
        }

    public function rolesDisponibles(int $id_usuario): array
        {
        $asignados = $this->db
            ->table('rel_usuarios_roles')
            ->select('id_rol')
            ->where('id_usuario', $id_usuario)
            ->get()
            ->getResultArray();

        $ids_asignados = array_column($asignados, 'id_rol');

        $query = $this->db->table('roles')->orderBy('nombre', 'ASC');

        if (!empty($ids_asignados))
            {
            $query->whereNotIn('id_rol', $ids_asignados);
            }

        return $query->get()->getResultObject();
        }

    public function asignarRol(int $id_usuario, int $id_rol): bool
        {
        $existe = $this->db
            ->table('rel_usuarios_roles')
            ->where('id_usuario', $id_usuario)
            ->where('id_rol', $id_rol)
            ->countAllResults();

        if ($existe) { return false; }

        return (bool) $this->db
            ->table('rel_usuarios_roles')
            ->insert(['id_usuario' => $id_usuario, 'id_rol' => $id_rol]);
        }

    public function quitarRol(int $id_usuario, int $id_rol): bool
        {
        return (bool) $this->db
            ->table('rel_usuarios_roles')
            ->where('id_usuario', $id_usuario)
            ->where('id_rol', $id_rol)
            ->delete();
        }
    public function emailExiste(string $email): bool
        {
        return $this->db
            ->table('usuarios')
            ->where('email', $email)
            ->countAllResults() > 0;
        }

    public function porToken(string $token): object|null
        {
        return $this->db
            ->table('usuarios')
            ->where('token_verificacion', $token)
            ->where('correo_verificado', false)
            ->get()
            ->getRow();
        }

    public function verificarCorreo(int $id_usuario): void
        {
        $this->db->table('usuarios')
            ->where('id_usuario', $id_usuario)
            ->update([
                'correo_verificado'  => true,
                'token_verificacion' => null,
            ]);
        }
    public function pendientesValidacion(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('usuarios u')
            ->select('u.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = u.id_evento', 'left')
            ->where('u.validado', false)
            ->orderBy('u.id_usuario', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('u.id_evento', $id_evento);
            }

        return $builder->get()->getResultObject();
        }

    public function validarUsuario(int $id_usuario): void
        {
        // Marcar como validado
        $this->db->table('usuarios')
            ->where('id_usuario', $id_usuario)
            ->update(['validado' => true]);

        // Buscar id del rol 'Presentador de Trabajo'
        $rol = $this->db->table('roles')
            ->where('nombre', 'Presentador de Trabajo')
            ->get()
            ->getRow();

        if (!$rol) { return; }

        // Asignar rol si no lo tiene ya
        $existe = $this->db->table('rel_usuarios_roles')
            ->where('id_usuario', $id_usuario)
            ->where('id_rol', $rol->id_rol)
            ->countAllResults();

        if (!$existe)
            {
            $this->db->table('rel_usuarios_roles')->insert([
                'id_usuario' => $id_usuario,
                'id_rol'     => $rol->id_rol,
            ]);
            }
        }
    
    public function buscarPorEmail(string $email): ?object
        {
        return $this->db
            ->table('usuarios')
            ->where('email', $email)
            ->get()
            ->getRow();
        }

    public function guardarTokenReset(int $id_usuario, string $token, string $expira): bool
        {
        return (bool) $this->db
            ->table('usuarios')
            ->where('id_usuario', $id_usuario)
            ->update([
                'reset_token'        => $token,
                'reset_token_expira' => $expira,
            ]);
        }

    public function buscarPorResetTokenValido(string $token): ?object
        {
        return $this->db
            ->table('usuarios')
            ->where('reset_token', $token)
            ->where('reset_token_expira >=', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();
        }

    public function actualizarPasswordConReset(int $id_usuario, string $passwordHash): bool
        {
        return (bool) $this->db
            ->table('usuarios')
            ->where('id_usuario', $id_usuario)
            ->update([
                'password'           => $passwordHash,
                'reset_token'        => null,
                'reset_token_expira' => null,
            ]);
        }



}
