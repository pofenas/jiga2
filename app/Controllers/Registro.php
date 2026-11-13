<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Registro extends \CodeIgniter\Controller
{
    // ----------------------------------------------------------------
    // GET: muestra el formulario de registro
    // ----------------------------------------------------------------
    public function getIndex()
        {
        $id_evento = (int) $this->request->getGet('evento');

        return view('registro/formulario', [
            'id_evento' => $id_evento,
        ]);
        }

    // ----------------------------------------------------------------
    // POST: procesa el registro
    // ----------------------------------------------------------------
    public function postIndex()
        {
        $model     = new UsuariosModel();
        $id_evento = (int) $this->request->getPost('id_evento');
        $email     = trim($this->request->getPost('email'));
        $nombre    = trim($this->request->getPost('nombre'));
        $password  = $this->request->getPost('password');
        $nombre_evento = '';
        
        // Verificar que el email no existe ya
        if ($model->emailExiste($email))
            {
            return redirect()->back()
                             ->with('error', 'Ya existe una cuenta con ese email.');
            }

        $token = bin2hex(random_bytes(32));

        $datos = (object) [
            'login'               => $email,
            'email'               => $email,
            'nombre'              => $nombre,
            'password'            => md5($password),
            'id_evento'           => $id_evento ?: null,
            'validado'            => false,
            'correo_verificado'   => false,
            'token_verificacion'  => $token,
        ];

        if ($model->crear($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al crear la cuenta.');
            }

        if ($id_evento)
            {
            $row = (new \App\Models\EventosModel())
                ->where('id_evento', $id_evento)
                ->get()
                ->getRow();
            $nombre_evento = $row->nombre ?? '';
            }

        $enlace = base_url('registro/verificar?token=' . $token);
        $this->enviarMailVerificacion($email, $nombre, $enlace, $nombre_evento);


        return view('registro/pendiente', [
            'email' => $email,
        ]);
        }

    // ----------------------------------------------------------------
    // GET: verificación del token
    // ----------------------------------------------------------------
    public function getVerificar()
        {
        $token = $this->request->getGet('token');
        $model = new UsuariosModel();

        $usuario = $model->porToken($token);

        if (!$usuario)
            {
            return view('registro/token_invalido');
            }

        $model->verificarCorreo($usuario->id_usuario);

        return view('registro/verificado');
        }

    // ----------------------------------------------------------------
    // Stub de envío de correo 
    // ----------------------------------------------------------------
    private function enviarMailVerificacion(
        string $email,
        string $nombre,
        string $enlace,
        string $nombre_evento = ''
     ): void
        {
        $mailer = \Config\Services::email();

        $mailer->setFrom('noreply@almansa.ovh', 'JIGA 2');
        $mailer->setTo($email);
        $mailer->setSubject('Verifica tu cuenta en ' . ($nombre_evento ?: 'JIGA 2'));
        $mailer->setMessage(view('emails/verificacion', [
            'nombre'        => $nombre,
            'enlace'        => $enlace,
            'nombre_evento' => $nombre_evento ?: 'JIGA 2',
        ]));

        if (!$mailer->send())
            {
            log_message('error', 'Error enviando verificación a ' . $email . ': ' . $mailer->printDebugger(['headers']));
            }
        }

}
