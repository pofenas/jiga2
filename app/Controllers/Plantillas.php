<?php

namespace App\Controllers;

use App\Models\PlantillasModel;

class Plantillas extends BaseController
{
    public function getListar()
        {
        $model     = new PlantillasModel();
        $esAdmin   = $this->user->tieneRol('Administrador');
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'plantillas/listar',
            'content_view' => 'layout/crud',
            'content_data' => [
                'titulo'        => 'Plantillas',
                'pk'            => 'id_plantilla',
                'ruta_base'     => base_url('plantillas'),
                'enctype'       => 'multipart/form-data',
                'col_descarga'  => 'filename',
                'ruta_descarga' => base_url('plantillas/descargar'),
                'columnas'      => [
                    ['campo' => 'id_plantilla',  'label' => 'ID'],
                    ['campo' => 'nombre',         'label' => 'Nombre'],
                    ['campo' => 'filename',       'label' => 'Archivo'],
                    ['campo' => 'privada',        'label' => 'Privada'],
                    ['campo' => 'nombre_evento',  'label' => 'Evento'],
                ],
                'acciones_extra' => [
                    ['label' => '📐 Calibrar', 'ruta' => base_url('plantillas/calibrar')],
                    ],
                'campos_form' => [
                    ['campo' => 'nombre',   'label' => 'Nombre',            'tipo' => 'text'],
                    ['campo' => 'privada',  'label' => 'Plantilla privada', 'tipo' => 'checkbox'],
                    

                    $esAdmin
                        ? [
                            'campo'    => 'id_evento',
                            'label'    => 'Evento',
                            'tipo'     => 'select',
                            'opciones' => $model->eventosDisponibles(),
                            'opt_val'  => 'id_evento',
                            'opt_txt'  => 'nombre',
                          ]
                        : [
                            'campo' => 'id_evento',
                            'label' => 'Evento',
                            'tipo'  => 'hidden',
                            'valor' => $id_evento,
                          ],
                    [
                        'campo'  => 'archivo',
                        'label'  => 'Archivo',
                        'tipo'   => 'file',
                        'accept' => '.pdf,.ppt,.pptx,.doc,.docx',
                        'nota'   => '(dejar vacío para no reemplazar)',
                    ],
                ],
                'filas' => $model->listar($id_evento),
            ],
        ]));
        }

    public function postCrear()
        {
        $model     = new PlantillasModel();
        $nombre    = trim((string) $this->request->getPost('nombre'));
        $id_evento = (int) $this->request->getPost('id_evento');
        $privada   = $this->request->getPost('privada') ? true : false;
        $archivo   = $this->request->getFile('archivo');

        if ($nombre === '')
            {
            return redirect()->back()
                            ->with('error', 'El nombre de la plantilla no puede estar vacío.');
            }

        if ($id_evento <= 0)
            {
            return redirect()->back()
                            ->with('error', 'Debes seleccionar un evento válido.');
            }

        if ($archivo && $archivo->isValid() && $archivo->getSize() > 0)
            {
            $id = $model->crearConArchivo(
                $nombre,
                $archivo->getClientName(),
                $id_evento,
                $privada,
                file_get_contents($archivo->getTempName())
            );
            }
        else
            {
            $id = $model->crearSinArchivo($nombre, $id_evento, $privada);
            }

        if ($id === false)
            {
            return redirect()->back()
                            ->with('error', 'Error al crear la plantilla.');
            }

        return redirect()->to(base_url('plantillas/listar'))
                        ->with('exito', 'Plantilla creada correctamente.');
        }

    public function postActualizar()
        {
        $model     = new PlantillasModel();
        $id        = (int) $this->request->getPost('id_plantilla');
        $nombre    = $this->request->getPost('nombre');
        $id_evento = (int) $this->request->getPost('id_evento');
        $privada   = $this->request->getPost('privada') ? true : false;
        $archivo   = $this->request->getFile('archivo');

        if ($archivo && $archivo->getError() === UPLOAD_ERR_INI_SIZE)
            {
            return redirect()->back()->with('error', 'El archivo es demasiado grande.');
            }

        if ($archivo && $archivo->isValid() && $archivo->getSize() > 0)
            {
            $ok = $model->actualizarConArchivo(
                $id,
                $nombre,
                $archivo->getClientName(),
                $id_evento,
                $privada,
                file_get_contents($archivo->getTempName())
            );
            }
        else
            {
            $ok = $model->actualizarSinArchivo($id, $nombre, $id_evento, $privada);
            }

        if ($ok === false)
            {
            header('Location: ' . base_url('plantillas/listar'));
            exit();
            }

        header('Location: ' . base_url('plantillas/listar'));
        exit();
        }

    public function postBorrar()
        {
        $model = new PlantillasModel();
        $id    = (int) $this->request->getPost('id_plantilla');

        if (empty($id))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar la plantilla.');
            }

        return redirect()->to(base_url('plantillas/listar'))
                         ->with('exito', 'Plantilla borrada correctamente.');
        }

    public function getDescargar(int $id)
        {
        $model = new PlantillasModel();

        $row = $model->db
            ->table('plantillas')
            ->select('filename, archivo')
            ->where('id_plantilla', $id)
            ->get()
            ->getRow();

        if (!$row)
            {
            return redirect()->to(base_url('plantillas/listar'))
                             ->with('error', 'Archivo no encontrado.');
            }

        $binario = pg_unescape_bytea($row->archivo);
        $ext     = strtolower(pathinfo($row->filename, PATHINFO_EXTENSION));
        $mime    = match($ext)
            {
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            default => 'application/octet-stream',
            };

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $row->filename . '"');
        header('Content-Length: ' . strlen($binario));
        header('Cache-Control: no-cache');
        echo $binario;
        exit();
        }
    public function getCalibrar(int $id)
        {
        $model = new PlantillasModel();
        $fila  = $model->listarUno($id);

        if (!$fila)
            {
            return redirect()->to(base_url('plantillas/listar'))
                            ->with('error', 'Plantilla no encontrada.');
            }

        return view('layout/main', $this->viewData([
            'active'       => 'plantillas/listar',
            'content_view' => 'plantillas/calibrar',
            'content_data' => [
                'plantilla' => $fila,
            ],
        ]));
        }

    public function postCalibrar()
        {
        $model = new PlantillasModel();
        $id    = (int) $this->request->getPost('id_plantilla');

        $model->actualizarCoordenadas(
            $id,
            (float) $this->request->getPost('pos_x_titulo'),
            (float) $this->request->getPost('pos_y_titulo'),
            (float) $this->request->getPost('pos_x_autores'),
            (float) $this->request->getPost('pos_y_autores'),
            (int)   $this->request->getPost('font_size')
        );

        return redirect()->to(base_url('plantillas/listar'))
                        ->with('exito', 'Coordenadas guardadas correctamente.');
        }
}



