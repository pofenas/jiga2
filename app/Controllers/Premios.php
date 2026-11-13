<?php

namespace App\Controllers;

use App\Models\PremiosModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class Premios extends BaseController
{
    protected array $roles_requeridos = ['Miembro Comité Organizador'];

    public function getListar(): string
        {
        $model     = new PremiosModel();
        $esAdmin   = $this->user->tieneRol('Administrador');
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'premios/listar',
            'content_view' => 'layout/crud',
            'content_data' => [
                'titulo'     => 'Premios',
                'pk'         => 'id',
                'ruta_base'  => base_url('premios'),
                'columnas'   => [
                    ['campo' => 'id',              'label' => 'ID'],
                    ['campo' => 'nombre',          'label' => 'Premio'],
                    ['campo' => 'nombre_categoria','label' => 'Categoría'],
                    ['campo' => 'nombre_premiado', 'label' => 'Trabajo premiado'],
                    ['campo' => 'nombre_evento',   'label' => 'Evento'],
                ],
                'campos_form' => [
                    [
                        'campo' => 'nombre',
                        'label' => 'Nombre',
                        'tipo'  => 'text',
                    ],
                    [
                        'campo'    => 'id_categoria',
                        'label'    => 'Categoría',
                        'tipo'     => 'select',
                        'opciones' => $model->categoriasDisponibles($id_evento),
                        'opt_val'  => 'id_categoria',
                        'opt_txt'  => 'nombre',
                    ],
                    
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
                ],
                'filas' => $model->listar($id_evento),
            ],
        ]));
        }

    public function postCrear(): RedirectResponse
        {
        $model = new PremiosModel();
        $datos = (object) $this->request->getPost();

        if (empty($datos->nombre))
            {
            return redirect()->back()
                             ->with('error', 'El nombre del premio no puede estar vacío.');
            }

        if (empty($datos->id_categoria))
            {
            return redirect()->back()
                             ->with('error', 'Debes seleccionar una categoría.');
            }

        if (empty($datos->id_evento))
            {
            return redirect()->back()
                             ->with('error', 'Debes seleccionar un evento válido.');
        }

        if (empty($datos->id_premiado))
            {
            $datos->id_premiado = null;
            }

        if ($model->crear($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al crear el premio.');
            }

        return redirect()->to(base_url('premios/listar'))
                         ->with('exito', 'Premio creado correctamente.');
        }

    public function postActualizar(): RedirectResponse
        {
        $model = new PremiosModel();
        $datos = (object) $this->request->getPost();

        if (empty($datos->id))
            {
            return redirect()->back()
                             ->with('error', 'ID no válido.');
            }

        if (empty($datos->nombre))
            {
            return redirect()->back()
                             ->with('error', 'El nombre del premio no puede estar vacío.');
            }

        if (empty($datos->id_categoria))
            {
            return redirect()->back()
                             ->with('error', 'Debes seleccionar una categoría.');
            }

        if (empty($datos->id_premiado))
            {
            $datos->id_premiado = null;
            }

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al actualizar el premio.');
            }

        return redirect()->to(base_url('premios/listar'))
                         ->with('exito', 'Premio actualizado correctamente.');
        }

    public function postBorrar(): RedirectResponse
        {
        $model = new PremiosModel();
        $id    = (int) $this->request->getPost('id');

        if (empty($id))
            {
            return redirect()->back()
                             ->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al borrar el premio.');
            }

        return redirect()->to(base_url('premios/listar'))
                         ->with('exito', 'Premio borrado correctamente.');
        }


    public function getAdjudicar(): string
        {
        $model     = new PremiosModel();
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'premios/adjudicar',
            'content_view' => 'premios/adjudicar',
            'content_data' => [
                'titulo'       => 'Adjudicación de premios',
                'premios'      => $model->listar($id_evento),
                'id_evento'    => $id_evento,
            ],
        ]));
        }

    public function getTrabajos(int $id_premio): ResponseInterface
        {
        $model    = new PremiosModel();
        $premio   = $model->buscar($id_premio);
        $id_evento = $this->idEventoFiltro();

        if (!$premio || ($id_evento !== null && (int) $premio->id_evento !== $id_evento))
            {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['ok' => false, 'error' => 'Premio no válido.']);
            }

        $trabajos = $model->trabajosOrdenados($id_premio);
        $detalles = $model->detallesCalificaciones($id_premio);

        foreach ($trabajos as $trabajo)
            {
            $trabajo->detalles = $detalles[$trabajo->id_trabajo] ?? [];
            }

        return $this->response->setJSON([
            'ok'       => true,
            'premio'   => $premio,
            'trabajos' => $trabajos,
        ]);
        }

    public function postAdjudicar(): ResponseInterface
        {
        $id_premio  = (int) $this->request->getPost('id_premio');
        $id_trabajo = $this->request->getPost('id_trabajo');

        $id_trabajo = $id_trabajo === null || $id_trabajo === ''
            ? null
            : (int) $id_trabajo;

        $model     = new PremiosModel();
        $premio    = $model->buscar($id_premio);
        $id_evento = $this->idEventoFiltro();

        if (!$premio || ($id_evento !== null && (int) $premio->id_evento !== $id_evento))
            {
            return $this->response
                ->setStatusCode(403)
                ->setJSON(['ok' => false, 'error' => 'Premio no válido.']);
            }

        if ($id_trabajo !== null)
            {
            $valido = $model->trabajoValidoParaPremio(
                $id_trabajo,
                (int) $premio->id_categoria,
                (int) $premio->id_evento
            );

            if (!$valido)
                {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON([
                        'ok' => false,
                        'error' => 'El trabajo no compete a este premio.'
                    ]);
                }
            }

        $model->asignar($id_premio, $id_trabajo);

        return $this->response->setJSON([
            'ok'         => true,
            'id_premio'  => $id_premio,
            'id_trabajo' => $id_trabajo,
        ]);
        }

        
}