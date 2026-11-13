<?php

namespace App\Models;

use CodeIgniter\Model;

class CrudModel extends Model
{
    protected $useTimestamps = false;
    protected $returnType    = 'object';

    // Propiedad genérica para el registro seleccionado
    public ?object $Registro = null;

    // ----------------------------------------------------------------
    // CONSTRUCTOR
    // Si se proporciona un id, carga el registro en $this->Registro.
    // ----------------------------------------------------------------
    public function __construct(?int $id = null)
        {
        parent::__construct();

        if ($id !== null)
            {
            $this->Registro = $this->find($id) ?? null;
            }
        }

    // ----------------------------------------------------------------
    // CREAR
    // ----------------------------------------------------------------
    public function crear(object $datos): int|false
        {
        $arr = (array) $datos;
        $arr = array_intersect_key($arr, array_flip($this->allowedFields));

        if ($this->insert($arr))
            {
            return (int) $this->getInsertID();
            }

        return false;
        }

    // ----------------------------------------------------------------
    // BORRAR
    // ----------------------------------------------------------------
    public function borrar(int $id): bool
        {
        return (bool) $this->delete($id);
        }

    // ----------------------------------------------------------------
    // ACTUALIZAR
    // ----------------------------------------------------------------
    public function actualizar(object $datos): bool
        {
        $arr = (array) $datos;

        if (empty($arr[$this->primaryKey]))
            {
            return false;
            }

        $id = (int) $arr[$this->primaryKey];
        unset($arr[$this->primaryKey]);

        $actualizar = array_intersect_key($arr, array_flip($this->allowedFields));

        if (empty($actualizar))
            {
            return true;
            }

        if ($this->find($id) === null)
            {
            return false;
            }

        return (bool) $this->update($id, $actualizar);
        }

    // ----------------------------------------------------------------
    // GUARDAR
    // Persiste $this->Registro en BD.
    // ----------------------------------------------------------------
    public function guardar(): bool
        {
        if ($this->Registro === null)
            {
            return false;
            }

        return $this->actualizar($this->Registro);
        }

    // ----------------------------------------------------------------
    // LISTAR
    // ----------------------------------------------------------------
    public function listar(): array
        {
        return $this->orderBy($this->primaryKey, 'ASC')->findAll();
        }
    }
