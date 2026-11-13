<?php

namespace App\Controllers;

use App\Models\PlantillasModel;

class Home extends BaseController
{
    public function index()
        {
        $esPresentador = in_array('Presentador de Trabajo', $this->user->roles);


        $plantillas = [];
        $trabajos   = [];
        $categorias = [];

        if ($esPresentador)
            {
            $modelP     = new PlantillasModel();
            $modelT     = new \App\Models\TrabajosModel();
            $id_evento  = $this->user->datos->id_evento;
            $id_usuario = $this->user->datos->id_usuario;

            $plantillas = $modelP->listar($id_evento, soloPublicas: true);
            $trabajos   = $modelT->listarDeUsuario($id_usuario);
            $categorias = $modelT->categoriasDeEvento($id_evento);
            }

        return view('layout/main', $this->viewData([
            'active'       => '',
            'content_view' => ($esPresentador ? 'home/dashboard' : ''),
            'content_data' => [
                'es_presentador' => $esPresentador,
                'plantillas'     => $plantillas,
                'trabajos'       => $trabajos,
                'categorias'     => $categorias,
            ],
        ]));
        }

}




