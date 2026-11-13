<?php

namespace App\Controllers;

use App\Models\UsuariosModel;

class Login extends \CodeIgniter\Controller
{
    public function getIndex()
        {
        if (session()->get('id_usuario'))
            {
            return redirect()->to(base_url('/'));
            }

        return view('login/index');
        }

    public function postIndex()
        {
        $login    = trim((string) $this->request->getPost('login'));
        $password = md5((string) $this->request->getPost('password'));

        $model = new UsuariosModel();

        $usuario = $model->where('login', $login)
                         ->where('password', $password)
                         ->first();

        if (!$usuario)
            {
            return view('login/index', [
                'error' => 'Usuario o contraseña incorrectos.',
            ]);
            }

        session()->set([
            'id_usuario' => $usuario->id_usuario,
            'login'      => $usuario->login,
            'nombre'     => $usuario->nombre,
        ]);

        return redirect()->to(base_url('/'));
        }

    public function getForgot()
        {
        return view('login/forgot');
        }

    public function postForgot()
        {
        $email = trim((string) $this->request->getPost('email'));
        $model = new UsuariosModel();
        $user  = $model->buscarPorEmail($email);

        if ($user)
            {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $model->guardarTokenReset((int) $user->id_usuario, $token, $expira);

            $enlace = base_url('login/reset?token=' . urlencode($token));
            $this->enviarMailRecuperacion($user->email, $user->nombre, $enlace);
            }

        return view('login/forgot_ok');
        }

    public function getReset()
        {
        $token = (string) $this->request->getGet('token');
        $model = new UsuariosModel();
        $user  = $model->buscarPorResetTokenValido($token);

        if (!$user)
            {
            return view('login/reset_invalido');
            }

        return view('login/reset', [
            'token' => $token,
        ]);
        }

    public function postReset()
        {
        $token           = trim((string) $this->request->getPost('token'));
        $password        = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        if ($password === '' || $password !== $passwordConfirm)
            {
            return view('login/reset', [
                'token' => $token,
                'error' => 'Las contraseñas no coinciden.',
            ]);
            }

        $model = new UsuariosModel();
        $user  = $model->buscarPorResetTokenValido($token);

        if (!$user)
            {
            return view('login/reset_invalido');
            }

        $model->actualizarPasswordConReset((int) $user->id_usuario, md5($password));

        return view('login/reset_ok');
        }

    public function getLogout()
        {
        session()->destroy();
        return redirect()->to(base_url('login'));
        }

    private function enviarMailRecuperacion(string $email, string $nombre, string $enlace): void
        {
        $mailer = \Config\Services::email();

        $mailer->setFrom('noreply@almansa.ovh', 'JIGA 2');
        $mailer->setTo($email);
        $mailer->setSubject('Recuperación de contraseña');
        $mailer->setMessage(view('emails/recuperar_password', [
            'nombre' => $nombre,
            'enlace' => $enlace,
        ]));

        if (!$mailer->send())
            {
            log_message('error', 'Error enviando recuperación a ' . $email . ': ' . $mailer->printDebugger(['headers']));
            }
        }
}
