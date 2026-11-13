<?php

namespace App\Models;

class CategoriasModel extends CrudModel
{
    protected $table         = 'categorias';
    protected $primaryKey    = 'id_categoria';
    protected $allowedFields = ['nombre', 'id_evento'];

    public function listar(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('categorias c')
            ->select('c.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = c.id_evento', 'left')
            ->orderBy('c.id_categoria', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('c.id_evento', $id_evento);
            }

        return $builder->get()->getResultObject();
        }

    public function eventosDisponibles(): array
        {
        return $this->db
            ->table('eventos')
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultObject();
        }
    }
