<?php
// app/Controllers/VerPresentacion.php
namespace App\Controllers;

use App\Models\PresentacionesModel;
use App\Models\TrabajosModel;
use CodeIgniter\Controller;

class VerPresentacion extends Controller
{
    protected array $roles_requeridos = []; // No se usa porque no hereda de BaseController

    public function getIndex(int $id_presentacion)
        {
        $model = new PresentacionesModel();

        $presentacion = $model->find($id_presentacion);
        if (!$presentacion)
            {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

        $trabajos = $model->trabajosDePresentacion($id_presentacion);

        return view('ver_presentacion/index', [
            'presentacion' => $presentacion,
            'trabajos'     => $trabajos,
        ]);
        }

    // *** NUEVO: Descarga PÚBLICA de PDF ***
    public function getDescargarPdf(int $id_trabajo)
        {
        $model = new TrabajosModel();
        $row = $model->db
            ->table('trabajos')
            ->select('pdf, filename, nombre')
            ->where('id_trabajo', $id_trabajo)
            ->get()
            ->getRow();

        if (!$row || empty($row->pdf))
            {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

        $binario = pg_unescape_bytea($row->pdf);

        header('Content-Type: application/pdf');
        // CAMBIAR ESTA LÍNEA:
        header('Content-Disposition: inline; filename="' . $row->filename . '"');
        //                     ↑ inline en lugar de attachment
        header('Content-Length: ' . strlen($binario));
        header('Cache-Control: no-cache');
        echo $binario;
        exit();
        }
}