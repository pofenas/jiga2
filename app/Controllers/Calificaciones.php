<?php

namespace App\Controllers;

use App\Models\CalificacionesModel;

class Calificaciones extends BaseController
{
    protected array $roles_requeridos = ['Miembro Comité Científico'];

    public function getIndex()
        {
        $model      = new CalificacionesModel();
        $id_usuario = $this->user->datos->id_usuario;
        $id_evento  = $this->user->datos->id_evento;

        if ($id_evento === null)
            [$trabajos, $items] = $model->todosSinFiltroEvento($id_usuario);
        else
            [$trabajos, $items] = $model->trabajosConMisCalificaciones($id_evento, $id_usuario);

        return view('layout/main', $this->viewData([
            'active'       => 'calificaciones',
            'content_view' => 'calificaciones/listar',
            'content_data' => [
                'trabajos' => $trabajos,
                'items'    => $items,
            ],
        ]));
        }
    public function getDescargar(int $id_trabajo)
        {
        $db        = \Config\Database::connect();
        $id_evento = $this->user->datos->id_evento;

        // Verificar que el trabajo pertenece al evento del usuario
        $trabajo = $db->table('trabajos t')
            ->join('categorias c', 'c.id_categoria = t.id_categoria')
            ->where('t.id_trabajo', $id_trabajo)
            ->where('c.id_evento',  $id_evento)
            ->get()->getRow();

        if (!$trabajo)
            {
            header('Location: ' . base_url('/'));
            exit();
            }

        $row = $db->query(
            'SELECT pdf, nombre FROM trabajos WHERE id_trabajo = ?', [$id_trabajo]
        )->getRow();

        if (!$row || empty($row->pdf))
            {
            header('Location: ' . base_url('/'));
            exit();
            }

        $binario = pg_unescape_bytea($row->pdf);

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $trabajo->nombre . '.pdf"');
        header('Content-Length: ' . strlen($binario));
        header('Cache-Control: no-cache');
        echo $binario;
        exit();
        }


    public function postGuardar()
        {
        $db         = \Config\Database::connect();
        $id_usuario = $this->user->datos->id_usuario;
        $id_evento  = $this->user->datos->id_evento;
        $id_trabajo = (int) $this->request->getPost('id_trabajo');
        $notas      = $this->request->getPost('notas');

        if (!$id_trabajo || !is_array($notas))
            {
            header('Location: ' . base_url('calificaciones') . '?error=1');
            exit();
            }

        $valido = $db->table('trabajos t')
            ->join('categorias c', 'c.id_categoria = t.id_categoria')
            ->where('t.id_trabajo', $id_trabajo)
            ->where('c.id_evento',  $id_evento)
            ->countAllResults();
        if (!$valido)
            {
            header('Location: ' . base_url('calificaciones') . '?error=1');
            exit();
            }

        $model = new CalificacionesModel();
        $model->guardarCalificaciones($id_usuario, $id_trabajo, $notas);

        header('Location: ' . base_url('calificaciones') . '?exito=1');
        exit();
        }
    public function postToggleAdmitido(): void
        {
            $idtrabajo = (int) $this->request->getPost('idtrabajo');

            if (!$idtrabajo)
            {
                $this->response->setStatusCode(400);
                echo json_encode(['ok' => false, 'error' => 'ID no válido']);
                exit;
            }

            // Verificar que el trabajo pertenece al evento del usuario
            $idevento = $this->user->datos->idevento;
            if ($idevento !== null)
            {
                $db      = \Config\Database::connect();
                $valido  = $db->table('trabajos t')
                    ->join('categorias c', 'c.idcategoria = t.idcategoria')
                    ->where('t.idtrabajo', $idtrabajo)
                    ->where('c.idevento', $idevento)
                    ->countAllResults();

                if (!$valido)
                {
                    $this->response->setStatusCode(403);
                    echo json_encode(['ok' => false, 'error' => 'No autorizado']);
                    exit;
                }
            }

            $model = new \App\Models\CalificacionesModel();
            $model->toggleAdmitido($idtrabajo);

            // Devolver el nuevo estado
            $db      = \Config\Database::connect();
            $trabajo = $db->table('trabajos')->select('admitido')->where('idtrabajo', $idtrabajo)->get()->getRow();

            echo json_encode(['ok' => true, 'admitido' => (bool) $trabajo->admitido]);
            exit;
        }




}
