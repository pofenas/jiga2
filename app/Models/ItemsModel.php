<?php

namespace App\Models;

class ItemsModel extends CrudModel
{
    protected $table         = 'items';
    protected $primaryKey    = 'id_item';
    protected $allowedFields = ['nombre', 'peso', 'id_evento'];

    public function listar(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('items i')
            ->select('i.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = i.id_evento', 'left')
            ->orderBy('i.id_item', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('i.id_evento', $id_evento);
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


