<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Usuarios extends BaseController
{
    protected array $roles_requeridos = ['Miembro Comité Organizador'];

    public function getListar()
        {
        $model     = new UsuariosModel();
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'usuarios/listar',
            'content_view' => 'usuarios/listar',
            'content_data' => [
                'titulo'    => 'Usuarios',
                'usuarios'  => $model->listar($id_evento),
                'eventos'   => $model->eventosDisponibles(),
                'es_admin'  => $this->user->tieneRol('Administrador'),
            ],
        ]));
        }


    public function postCrear()
        {
        $model = new UsuariosModel();
        $datos = (object) $this->request->getPost();

        if (!empty($datos->password))
            {
            $datos->password = md5($datos->password);
            }

        // Si no es Administrador, forzar el id_evento del usuario en sesión
        if (!$this->user->tieneRol('Administrador'))
            {
            $datos->id_evento = $this->user->datos->id_evento;
            }

        if ($model->crear($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al crear el usuario.');
            }

        return redirect()->to(base_url('usuarios/listar'))
                        ->with('exito', 'Usuario creado correctamente.');
        }

    public function postActualizar()
        {
        $model = new UsuariosModel();
        $datos = (object) $this->request->getPost();

        if (!empty($datos->password))
            {
            $datos->password = md5($datos->password);
            }
        else
            {
            unset($datos->password);
            }

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al actualizar el usuario.');
            }

        return redirect()->to(base_url('usuarios/listar'))
                         ->with('exito', 'Usuario actualizado correctamente.');
        }

    public function postBorrar()
        {
        $model = new UsuariosModel();
        $id    = (int) $this->request->getPost('id_usuario');

        if (empty($id))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar el usuario.');
            }

        return redirect()->to(base_url('usuarios/listar'))
                         ->with('exito', 'Usuario borrado correctamente.');
        }

    public function postAsignarRol()
        {
        $model      = new UsuariosModel();
        $id_usuario = (int) $this->request->getPost('id_usuario');
        $id_rol     = (int) $this->request->getPost('id_rol');

        if (empty($id_usuario) || empty($id_rol))
            {
            return redirect()->back()->with('error', 'Datos incompletos.');
            }

        $model->asignarRol($id_usuario, $id_rol);

        return redirect()->to(base_url('usuarios/listar'))
                         ->with('exito', 'Rol asignado correctamente.');
        }

    public function postQuitarRol()
        {
        $model      = new UsuariosModel();
        $id_usuario = (int) $this->request->getPost('id_usuario');
        $id_rol     = (int) $this->request->getPost('id_rol');

        if (empty($id_usuario) || empty($id_rol))
            {
            return redirect()->back()->with('error', 'Datos incompletos.');
            }

        $model->quitarRol($id_usuario, $id_rol);

        return redirect()->to(base_url('usuarios/listar'))
                         ->with('exito', 'Rol eliminado correctamente.');
        }

    public function getRolesDisponibles(int $id_usuario)
        {
        $model = new UsuariosModel();

        $roles = $model->rolesDisponibles($id_usuario);

        if (!$this->user->tieneRol('Administrador'))
            {
            $roles = array_filter($roles, fn($r) => $r->nombre !== 'Administrador');
            $roles = array_values($roles);
            }

        return $this->response->setJSON($roles);
        }

    }
