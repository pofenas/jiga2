<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Validacion extends BaseController
{
    protected array $roles_requeridos = ['Miembro Comité Organizador'];

    public function getListar()
        {
        $model     = new UsuariosModel();
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'validacion/listar',
            'content_view' => 'validacion/listar',
            'content_data' => [
                'titulo'   => 'Usuarios pendientes de validación',
                'usuarios' => $model->pendientesValidacion($id_evento),
            ],
        ]));
        }

    public function postValidar()
        {
        $model      = new UsuariosModel();
        $id_usuario = (int) $this->request->getPost('id_usuario');

        if (empty($id_usuario))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        $usuario = $model->find($id_usuario);

        if (!$usuario)
            {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
            }

        // Marcar como validado y asignar rol
        $model->validarUsuario($id_usuario);

        // Enviar email de bienvenida
        $this->enviarMailAdmitido(
            $usuario->email,
            $usuario->nombre,
            base_url('login')
        );

        return redirect()->to(base_url('validacion/listar'))
                         ->with('exito', 'Usuario validado correctamente.');
        }

    public function postEliminar()
        {
        $model      = new UsuariosModel();
        $id_usuario = (int) $this->request->getPost('id_usuario');

        if (empty($id_usuario))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id_usuario) === false)
            {
            return redirect()->back()->with('error', 'Error al eliminar el usuario.');
            }

        return redirect()->to(base_url('validacion/listar'))
                         ->with('exito', 'Usuario eliminado correctamente.');
        }

    private function enviarMailAdmitido(
        string $email,
        string $nombre,
        string $enlace
     ): void
        {
        $mailer = \Config\Services::email();

        $mailer->setFrom('noreply@almansa.ovh', 'JIGA 2');
        $mailer->setTo($email);
        $mailer->setSubject('Tu cuenta ha sido activada');
        $mailer->setMessage(view('emails/admitido', [
            'nombre' => $nombre,
            'enlace' => $enlace,
        ]));

        if (!$mailer->send())
            {
            log_message('error', 'Error enviando admisión a ' . $email . ': ' . $mailer->printDebugger(['headers']));
            }
        }
    }
