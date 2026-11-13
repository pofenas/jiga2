<?php

namespace App\Controllers;

use App\Models\ItemsModel;

class Items extends BaseController
{
    public function getListar()
        {
        $model     = new ItemsModel();
        $esAdmin   = $this->user->tieneRol('Administrador');
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'items/listar',
            'content_view' => 'layout/crud',
            'content_data' => [
                'titulo'    => 'Items',
                'pk'        => 'id_item',
                'ruta_base' => base_url('items'),
                'columnas'  => [
                    ['campo' => 'id_item',       'label' => 'ID'],
                    ['campo' => 'nombre',         'label' => 'Nombre'],
                    ['campo' => 'peso',           'label' => 'Peso'],
                    ['campo' => 'nombre_evento',  'label' => 'Evento'],
                ],
                'campos_form' => [
                    ['campo' => 'nombre', 'label' => 'Nombre', 'tipo' => 'text'],
                    ['campo' => 'peso',   'label' => 'Peso',   'tipo' => 'number', 'step' => '0.01'],
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

    public function postCrear()
        {
        $model = new ItemsModel();
        $datos = (object) $this->request->getPost();

        if ($model->crear($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al crear el item.');
            }

        return redirect()->to(base_url('items/listar'))
                         ->with('exito', 'Item creado correctamente.');
        }

    public function postActualizar()
        {
        $model = new ItemsModel();
        $datos = (object) $this->request->getPost();

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al actualizar el item.');
            }

        return redirect()->to(base_url('items/listar'))
                         ->with('exito', 'Item actualizado correctamente.');
        }

    public function postBorrar()
        {
        $model = new ItemsModel();
        $id    = (int) $this->request->getPost('id_item');

        if (empty($id))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar el item.');
            }

        return redirect()->to(base_url('items/listar'))
                         ->with('exito', 'Item borrado correctamente.');
        }
    }
