<?php

namespace App\Controllers;

use App\Models\CategoriasModel;

class Categorias extends BaseController
{
    public function getListar()
    {
    $model     = new CategoriasModel();
    $esAdmin   = $this->user->tieneRol('Administrador');
    $id_evento = $this->idEventoFiltro();

    return view('layout/main', $this->viewData([
        'active'       => 'categorias/listar',
        'content_view' => 'layout/crud',
        'content_data' => [
            'titulo'       => 'Categorías',
            'pk'           => 'id_categoria',
            'ruta_base'    => base_url('categorias'),
            'columnas'     => [
                ['campo' => 'id_categoria', 'label' => 'ID'],
                ['campo' => 'nombre',       'label' => 'Nombre'],
                ['campo' => 'nombre_evento','label' => 'Evento'],
            ],
            'campos_form'  => [
                ['campo' => 'nombre',    'label' => 'Nombre',  'tipo' => 'text'],
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
                        'campo'  => 'id_evento',
                        'label'  => 'Evento',
                        'tipo'   => 'hidden',
                        'valor'  => $id_evento,
                      ],
            ],
            'filas' => $model->listar($id_evento),
        ],
    ]));
    }


    public function postCrear()
        {
        $model = new CategoriasModel();
        $datos = (object) $this->request->getPost();

        if ($model->crear($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al crear la categoría.');
            }

        return redirect()->to(base_url('categorias/listar'))
                         ->with('exito', 'Categoría creada correctamente.');
        }

    public function postActualizar()
        {
        $model = new CategoriasModel();
        $datos = (object) $this->request->getPost();

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al actualizar la categoría.');
            }

        return redirect()->to(base_url('categorias/listar'))
                         ->with('exito', 'Categoría actualizada correctamente.');
        }

    public function postBorrar()
        {
        $model = new CategoriasModel();
        $id    = (int) $this->request->getPost('id_categoria');

        if (empty($id))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar la categoría.');
            }

        return redirect()->to(base_url('categorias/listar'))
                         ->with('exito', 'Categoría borrada correctamente.');
        }
    }
