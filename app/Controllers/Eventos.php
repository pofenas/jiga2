<?php

namespace App\Controllers;

use App\Models\EventosModel;
use App\Models\User;

class Eventos extends BaseController
{
    protected array $roles_requeridos = ['Administrador'];
    public function getListar()
        {
        $model = new EventosModel();

        return view('layout/main', $this->viewData([
            'menu'         => config('AppMenu')->menu,
            'active'       => 'eventos/listar',
            'content_view' => 'layout/crud',
            'content_data' => [
                'titulo'    => 'Eventos',
                'pk'        => 'id_evento',
                'ruta_base' => base_url('eventos'),
                'columnas'  => [
                    ['campo' => 'id_evento', 'label' => 'ID'],
                    ['campo' => 'nombre',    'label' => 'Nombre'],
                ],
                'filas' => $model->listar(),
            ],
        ]));
        }

    // ----------------------------------------------------------------
    // POST CREAR
    // Recibe los datos del formulario y crea un nuevo registro.
    // ----------------------------------------------------------------
    public function postCrear()
        {
        $model = new EventosModel();

        $datos = (object) $this->request->getPost();

        $id = $model->crear($datos);

        if ($id === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al crear el registro.');
            }

        return redirect()->to(base_url('eventos/listar'))
                         ->with('exito', 'Registro creado correctamente.');
        }

    // ----------------------------------------------------------------
    // POST ACTUALIZAR
    // Recibe los datos del formulario (incluido el id) y actualiza.
    // ----------------------------------------------------------------
    public function postActualizar()
        {
        $model = new EventosModel();

        $datos = (object) $this->request->getPost();

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al actualizar el registro.');
            }

        return redirect()->to(base_url('eventos/listar'))
                         ->with('exito', 'Registro actualizado correctamente.');
        }

    // ----------------------------------------------------------------
    // POST BORRAR
    // Recibe el id por POST y elimina el registro.
    // ----------------------------------------------------------------
    public function postBorrar()
        {
        $model = new EventosModel();

        $id = (int) $this->request->getPost('id_evento');

        if (empty($id))
            {
            return redirect()->back()
                             ->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al borrar el registro.');
            }

        return redirect()->to(base_url('eventos/listar'))
                         ->with('exito', 'Registro borrado correctamente.');
        }
    }
