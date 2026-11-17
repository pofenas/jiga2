<?php

namespace App\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\EventosModel;
use App\Models\PresentacionesModel;
use Endroid\QrCode\Color\Color;

class ListaEventos extends BaseController
{
    public function getIndex()
        {
        $eventosModel        = new EventosModel();
        $presentacionesModel = new PresentacionesModel();

        $esAdministrador = $this->user->tieneRol('Administrador');

        if ($esAdministrador)
            {
            $eventos = $eventosModel
                ->orderBy('id_evento', 'DESC')
                ->findAll();
            }
        else
            {
            $idEventoUsuario = (int) ($this->user->datos->id_evento ?? 0);

            $eventos = $idEventoUsuario > 0
                ? $eventosModel->where('id_evento', $idEventoUsuario)->findAll()
                : [];
            }

        foreach ($eventos as $evento)
            {
            $evento->url_trabajos = base_url('registro?evento=' . $evento->id_evento);
            $evento->qr_trabajos  = base_url('lista-eventos/qr/' . $evento->id_evento);

            $evento->presentaciones = $presentacionesModel
                ->where('id_evento', $evento->id_evento)
                ->orderBy('id_presentacion', 'ASC')
                ->findAll();

            foreach ($evento->presentaciones as $presentacion)
                {
                $presentacion->url_qr = base_url('presentaciones/qr/' . $presentacion->id_presentacion);
                $presentacion->url_publica = base_url('ver-presentacion/' . $presentacion->id_presentacion);

                $presentacion->url_logo = !empty($presentacion->logo)
                    ? base_url('assets/img/' . $presentacion->logo)
                    : null;
                }
            }

        return view('layout/main', $this->viewData([
            'active'       => 'lista_eventos',
            'content_view' => 'lista_eventos/index',
            'content_data' => [
                'titulo'  => 'Eventos y presentaciones',
                'eventos' => $eventos,
            ],
        ]));
        }

    public function getQr(int $id_evento)
        {
        $model = new PresentacionesModel();

        $presentacion = $model
            ->where('id_evento', $id_evento)
            ->orderBy('id_presentacion', 'ASC')
            ->first();

        if (!$presentacion || empty($presentacion->logo))
            {
            header('Content-Type: text/plain; charset=utf-8');
            echo 'ERROR: No hay ninguna presentación con logo para el evento ' . $id_evento;
            exit();
            }

        $rutaLogo = FCPATH . 'assets/img/' . $presentacion->logo;

        if (!is_file($rutaLogo))
            {
            header('Content-Type: text/plain; charset=utf-8');
            echo "ERROR: No se encuentra el logo en:\n{$rutaLogo}";
            exit();
            }

        $url = base_url('registro?evento=' . $id_evento);

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $url,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 600,
            margin: 20,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255),
            logoPath: $rutaLogo,
            logoResizeToWidth: 120,
            logoPunchoutBackground: false
        );

        $resultado = $builder->build();

        header('Content-Type: ' . $resultado->getMimeType());
        header('Cache-Control: no-cache');

        echo $resultado->getString();
        exit();
        }
}