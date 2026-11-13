<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\User;



/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    protected User $user;
    protected array $roles_requeridos = [];
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
        {
            // Load here all helpers you want to be available in your controllers that extend BaseController.
            // Caution: Do not put the this below the parent::initController() call below.
            // $this->helpers = ['form', 'url'];

            // Caution: Do not edit this line.
            parent::initController($request, $response, $logger);
            $this->user = new User();
             if (!$this->user->tieneRol($this->roles_requeridos))
                {
                header('Location: ' . base_url('/'));
                exit();
                }

            // Preload any models, libraries, etc, here.
            // $this->session = service('session');
        }
    protected function idEventoFiltro(): ?int
        {
        return $this->user->tieneRol('Administrador')
            ? null
            : (int) $this->user->datos->id_evento;
        }

    protected function viewData(array $extra = []): array
        {
        return array_merge([
            'menu' => config('AppMenu')->menu,
            'user' => $this->user,
        ], $extra);
        }
}
