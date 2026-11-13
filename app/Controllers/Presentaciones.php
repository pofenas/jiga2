<?php
namespace App\Controllers;

use App\Models\PresentacionesModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;

use CodeIgniter\HTTP\RedirectResponse;

class Presentaciones extends BaseController
{
    protected array $roles_requeridos = ['Miembro Comité Organizador'];

    public function getListar(): string
        {
        $model     = new PresentacionesModel();
        $id_evento = $this->idEventoFiltro();

        return view('layout/main', $this->viewData([
            'active'       => 'presentaciones/listar',
            'content_view' => 'presentaciones/listar',
            'content_data' => [
                'titulo'         => 'Presentaciones',
                'presentaciones' => $model->listar($id_evento),
            ],
        ]));
        }

    
    public function getQr(int $id_presentacion) // *** Generar QR público ***
        {
        $model = new PresentacionesModel();
        $presentacion = $model->find($id_presentacion);

        if (!$presentacion)
            {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }

        $url = base_url("ver-presentacion/{$id_presentacion}");

        $rutaLogo = null;

        if (!empty($presentacion->logo))
            {
            $rutaLogo = FCPATH . 'assets/img/' . $presentacion->logo;
            }

        if (!is_file($rutaLogo))
            {
            header('Content-Type: text/plain; charset=utf-8');
            echo "ERROR: No se encuentra el logo en:\n{$rutaLogo}";
            exit();
            }

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
    public function postCrear(): RedirectResponse
        {
        $model  = new PresentacionesModel();
        $nombre = trim((string) $this->request->getPost('nombre'));

        $id_evento = $this->user->tieneRol('Administrador')
            ? (int) $this->request->getPost('id_evento')
            : $this->idEventoFiltro();

        if ($nombre === '')
            {
            return redirect()->back()
                            ->with('error', 'El nombre no puede estar vacío.');
            }

        if ($id_evento <= 0)
            {
            return redirect()->back()
                            ->with('error', 'Debes seleccionar un evento válido.');
            }

        $logo = $this->procesarLogo($this->request->getFile('logo'));

        $datos = (object) [
            'nombre'    => $nombre,
            'id_evento' => $id_evento,
            'logo'      => $logo,
        ];

        if (!$model->crear($datos))
            {
            return redirect()->back()
                            ->with('error', 'Error al crear la presentación.');
            }

        return redirect()->to(base_url('presentaciones/listar'))
                        ->with('exito', 'Presentación creada correctamente.');
        }

    public function postActualizar()
        {
        $model = new PresentacionesModel();
        $datos = (object)$this->request->getPost();

        $anterior = $model->find((int) $datos->id_presentacion);

        if ($anterior && !empty($anterior->logo))
            {
            $rutaAnterior = FCPATH . 'assets/img/' . $anterior->logo;

            if (is_file($rutaAnterior))
                {
                unlink($rutaAnterior);
                }
            }        
        $logo = $this->procesarLogo($this->request->getFile('logo'));

        $datos = (object) $this->request->getPost();

        if ($logo !== null)
            {
            $datos->logo = $logo;
            }

        if (!$model->actualizar($datos))
            {
            redirect()->back()->with('error', 'Error al actualizar la presentación.')->send();
            return;
            }
        if ($model->actualizar($datos) === false)
            {
            return redirect()->back()->with('error', 'Error al actualizar la presentación.');
            }

        return redirect()->to(base_url('presentaciones/listar'))
                         ->with('exito', 'Presentación actualizada correctamente.');
        }

    public function postBorrar()
        {
        $model = new PresentacionesModel();
        $id    = (int)$this->request->getPost('id_presentacion');

        if (empty($id))
            {
            return redirect()->back()->with('error', 'ID no válido.');
            }

        if ($model->borrar($id) === false)
            {
            return redirect()->back()->with('error', 'Error al borrar la presentación.');
            }

        return redirect()->to(base_url('presentaciones/listar'))
                         ->with('exito', 'Presentación borrada correctamente.');
        }

    
    public function getTrabajos(int $id_presentacion)// Devuelve JSON con los trabajos admitidos del evento para el modal
        {
        $model     = new PresentacionesModel();
        $id_evento = (int)$this->user->datos->id_evento;

        $trabajos = $model->trabajosAdmitidos($id_evento, $id_presentacion);

        return $this->response->setJSON($trabajos);
        }

    
    public function postGuardarTrabajos()// Guarda la selección y orden de trabajos para una presentación
        {
        $model          = new PresentacionesModel();
        $id_presentacion = (int)$this->request->getPost('id_presentacion');
        $ids_trabajos   = $this->request->getPost('trabajos') ?? [];

        if (empty($id_presentacion))
            {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false]);
            }

        $model->guardarTrabajos($id_presentacion, $ids_trabajos);

        return $this->response->setJSON(['ok' => true]);
        }
        
    private function procesarLogo($archivo): ?string
        {
        if (!$archivo || !$archivo->isValid() || $archivo->getSize() === 0)
            {
            return null;
            }

        if (!in_array($archivo->getClientMimeType(), ['image/png', 'image/jpeg', 'image/webp'], true))
            {
            return null;
            }

        $extension = strtolower($archivo->getClientExtension());

        if (!in_array($extension, ['png', 'jpg', 'jpeg', 'webp'], true))
            {
            $extension = match ($archivo->getClientMimeType())
                {
                'image/png'  => 'png',
                'image/jpeg' => 'jpg',
                'image/webp' => 'webp',
                default      => 'png',
                };
            }

        $nombreArchivo = 'logo_' . bin2hex(random_bytes(16)) . '.' . $extension;

        $rutaDestino = FCPATH . 'assets/img';

        if (!is_dir($rutaDestino))
            {
            mkdir($rutaDestino, 0755, true);
            }

        $archivo->move($rutaDestino, $nombreArchivo);

        return $nombreArchivo;
        }

}
