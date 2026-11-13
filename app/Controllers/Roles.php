<?php

namespace App\Controllers;

use App\Models\RolesModel;

class Roles extends BaseController
{
    public function getListar()
        {
        $model = new RolesModel();

        return view('layout/main', $this->viewdata([
            'menu'         => config('AppMenu')->menu,
            'active'       => 'roles/listar',
            'content_view' => 'layout/crud',
            'content_data' => [
                'titulo'    => 'Roles',
                'pk'        => 'id_rol',
                'ruta_base' => base_url('roles'),
                'columnas'  => [
                    ['campo' => 'id_rol',  'label' => 'ID'],
                    ['campo' => 'nombre',  'label' => 'Nombre'],
                ],
                'filas' => $model->listar(),
            ],
        ]));
        }

    public function postCrear()
        {
        $model = new RolesModel();
        $datos = (object) $this->request->getPost();

        if ($model->crear($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al crear el registro.');
            }

        return redirect()->to(base_url('roles/listar'))
                         ->with('exito', 'Registro creado correctamente.');
        }

    public function postActualizar()
        {
        $model = new RolesModel();
        $datos = (object) $this->request->getPost();

        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()
                             ->with('error', 'Error al actualizar el registro.');
            }

        return redirect()->to(base_url('roles/listar'))
                         ->with('exito', 'Registro actualizado correctamente.');
        }

    public function postBorrar()
        {
        $model  = new RolesModel();
        $id     = (int) $this->request->getPost('id_rol');

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

        return redirect()->to(base_url('roles/listar'))
                         ->with('exito', 'Registro borrado correctamente.');
        }
    }
