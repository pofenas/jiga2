<?php

namespace App\Controllers;

use App\Models\TrabajosModel;

class Trabajos extends BaseController
{
    protected array $roles_requeridos = ['Presentador de Trabajo'];

    private function enviarMailPresentador(
        string $email,
        string $nombre_usuario,
        int    $id_trabajo,
        string $nombre_trabajo,
        string $lista_autores,
        string $filename
        ): void
        {
        $mailer = \Config\Services::email();
        $mailer->setFrom('noreply@almansa.ovh', 'JIGA 2');
        $mailer->setTo($email);
        $mailer->setSubject('Trabajo registrado: ' . $nombre_trabajo);
        $mailer->setMessage(view('emails/trabajo_presentado', [
            'nombre_usuario' => $nombre_usuario,
            'id_trabajo'     => $id_trabajo,
            'nombre_trabajo' => $nombre_trabajo,
            'lista_autores'  => $lista_autores,
            'filename'       => $filename,
            'fecha'          => date('d/m/Y H:i'),
        ]));

        if (!$mailer->send())
            {
            log_message('error', 'Error enviando confirmación trabajo a ' . $email);
            }
        }



    private function enviarMailComiteCientifico(
        object $miembro,
        string $nombre_trabajo
        ): void
        {
        $mailer = \Config\Services::email();
        $mailer->setFrom('noreply@almansa.ovh', 'JIGA 2');
        $mailer->setTo($miembro->email);
        $mailer->setSubject('Nuevo trabajo pendiente de evaluación');
        $mailer->setMessage(view('emails/trabajo_pendiente_evaluacion', [
            'nombre_miembro' => $miembro->nombre,
            'nombre_trabajo' => $nombre_trabajo,
        ]));

        if (!$mailer->send())
            {
            log_message('error', 'Error enviando notif. comité a ' . $miembro->email);
            }
        }


    private function procesarPdf(?object $archivo): ?array
        {
        if (!$archivo)
            {
            return null;
            }

        // PRIMERO: comprobar errores de tamaño antes de cualquier otra cosa
        $err = $archivo->getError();
        if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE)
            {
            throw new \RuntimeException('grande');
            }

        // Sin archivo seleccionado
        if ($err === UPLOAD_ERR_NO_FILE || $archivo->getSize() === 0)
            {
            return null;
            }

        // Cualquier otro error de subida
        if (!$archivo->isValid())
            {
            throw new \RuntimeException('grande');
            }

        if (strtolower($archivo->getClientExtension()) !== 'pdf')
            {
            throw new \RuntimeException('formato');
            }

        return [
            'filename' => $archivo->getClientName(),
            'binario'  => file_get_contents($archivo->getTempName()),
        ];
        }

    public function postCrear()
        {
        $model         = new TrabajosModel();
        $nombre        = trim($this->request->getPost('nombre'));
        $lista_autores = trim($this->request->getPost('lista_autores'));
        $id_categoria  = (int) $this->request->getPost('id_categoria');
        $id_usuario    = $this->user->datos->id_usuario;

        // Si post_max_size se superó, $_POST llega vacío
        if (empty($nombre) && empty($_FILES))
            {
            return redirect()->to(base_url('/'))
                            ->with('error', 'El archivo supera el tamaño máximo permitido por el servidor.');
            }

        try
            {
            $pdf = $this->procesarPdf($this->request->getFile('pdf'));
            }
        catch (\RuntimeException $e)
            {
            $msg = $e->getMessage() === 'grande'
                ? 'El archivo es demasiado grande.'
                : 'Solo se admiten archivos PDF.';

            return redirect()->to(base_url('/'))
                            ->with('error', $msg);
            }

        if (!$pdf)
            {
            return redirect()->to(base_url('/'))->with('error', 'Debes adjuntar un archivo PDF.');
            }

        $ok          = $model->crearConPdf(
            $nombre, $lista_autores, $id_categoria,
            $id_usuario, $pdf['filename'], $pdf['binario']
        );
        $filename_pdf = $pdf['filename'];
        unset($pdf);

        if ($ok === false)
            {
            return redirect()->to(base_url('/'))->with('error', 'Error al guardar el trabajo.');
            }

        $this->enviarMailPresentador(
            $this->user->datos->email,
            $this->user->datos->nombre,
            $ok,
            $nombre,
            $lista_autores,
            $filename_pdf
        );

        $miembros = $model->miembrosComiteCientifico($this->user->datos->id_evento);
        foreach ($miembros as $miembro)
            {
            $this->enviarMailComiteCientifico($miembro, $nombre);
            }

        return redirect()->to(base_url('/'))->with('exito', 'Trabajo presentado correctamente.');
        }

    public function postActualizar()
        {
        $model         = new TrabajosModel();
        $id            = (int) $this->request->getPost('id_trabajo');
        $nombre        = trim($this->request->getPost('nombre'));
        $lista_autores = trim($this->request->getPost('lista_autores'));
        $id_categoria  = (int) $this->request->getPost('id_categoria');

        // Verificar que el trabajo pertenece al usuario
        $trabajo = $model->find($id);
        if (!$trabajo || $trabajo->id_usuario != $this->user->datos->id_usuario)
            {
            return redirect()->to(base_url('/'));
            }

        // Guard contra post_max_size superado
        if (!empty($_SERVER['CONTENT_LENGTH']) &&
            $_SERVER['CONTENT_LENGTH'] > (int) ini_get('post_max_size') * 1024 * 1024)
            {
            return redirect()->to(base_url('/'))
                            ->with('error', 'El archivo supera el tamaño máximo permitido por el servidor.');
            }

        try
            {
            $pdf = $this->procesarPdf($this->request->getFile('pdf'));
            }
        catch (\RuntimeException $e)
            {
            $msg = $e->getMessage() === 'grande'
                ? 'El archivo es demasiado grande.'
                : 'Solo se admiten archivos PDF.';

            return redirect()->to(base_url('/'))->with('error', $msg);
            }

        if ($pdf)
            {
            $ok = $model->actualizarConPdf(
                $id, $nombre, $lista_autores, $id_categoria,
                $pdf['filename'], $pdf['binario']
            );
            unset($pdf);
            }
        else
            {
            $ok = $model->actualizarSinPdf($id, $nombre, $lista_autores, $id_categoria);
            }

        return redirect()->to(base_url('/'))
                        ->with($ok ? 'exito' : 'error',
                                $ok ? 'Trabajo actualizado correctamente.' : 'Error al actualizar el trabajo.');
        }

    

    public function postBorrar()
        {
        $model = new TrabajosModel();
        $id    = (int) $this->request->getPost('id_trabajo');

        $trabajo = $model->find($id);
        if (!$trabajo || $trabajo->id_usuario != $this->user->datos->id_usuario)
            {
            return redirect()->back()->with('error', 'No autorizado.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar el trabajo.');
            }

        return redirect()->to(base_url('/'))
                         ->with('exito', 'Trabajo eliminado correctamente.');
        }

    public function getDescargar(int $id)
        {
        $model = new TrabajosModel();
        $row   = $model->descargarPdf($id);

        if (!$row || empty($row->pdf))
            {
            return redirect()->to(base_url('/'))
                             ->with('error', 'Archivo no encontrado.');
            }

        $binario = pg_unescape_bytea($row->pdf);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $row->filename . '"');
        header('Content-Length: ' . strlen($binario));
        header('Cache-Control: no-cache');
        echo $binario;
        exit();
        }
    }
