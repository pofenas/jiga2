<?php
namespace App\Models;

class PresentacionesModel extends CrudModel
{
    protected $table      = 'presentaciones';
    protected $primaryKey = 'id_presentacion';
    protected $allowedFields = ['nombre', 'id_evento', 'logo'];

    public function listar(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('presentaciones')
            ->orderBy('id_presentacion', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('id_evento', $id_evento);
            }

        return $builder->get()->getResultObject();
        }

    public function trabajosAdmitidos(int $id_evento, int $id_presentacion): array
        {
        $todos = $this->db
            ->table('trabajos t')
            ->select('t.id_trabajo, t.nombre, t.lista_autores')
            ->join('categorias c', 'c.id_categoria = t.id_categoria')
            ->where('c.id_evento', $id_evento)
            ->where('t.admitido', true)
            ->orderBy('t.nombre', 'ASC')
            ->get()
            ->getResultObject();

        $enPresentacion = $this->db
            ->table('rel_presentaciones_trabajos')
            ->select('id_trabajo, orden')
            ->where('id_presentacion', $id_presentacion)
            ->orderBy('orden', 'ASC')
            ->get()
            ->getResultArray();

        $indexados = array_column($enPresentacion, 'orden', 'id_trabajo');

        foreach ($todos as $t)
            {
            $t->incluido = isset($indexados[$t->id_trabajo]);
            $t->orden    = $indexados[$t->id_trabajo] ?? 9999;
            }

        usort($todos, function($a, $b)
            {
            if ($a->incluido !== $b->incluido)
                {
                return $b->incluido <=> $a->incluido;
                }
            return $a->orden <=> $b->orden;
            });

        return $todos;
        }

    public function guardarTrabajos(int $id_presentacion, array $ids_trabajos): void
        {
        $this->db->table('rel_presentaciones_trabajos')
            ->where('id_presentacion', $id_presentacion)
            ->delete();

        foreach ($ids_trabajos as $orden => $id_trabajo)
            {
            $this->db->table('rel_presentaciones_trabajos')->insert(
                [
                'id_presentacion' => $id_presentacion,
                'id_trabajo'      => (int) $id_trabajo,
                'orden'           => $orden + 1,
                ]
            );
            }
        }

    public function trabajosDePresentacion(int $id_presentacion): array
        {
        return $this->db
            ->table('rel_presentaciones_trabajos rpt')
            ->select('t.id_trabajo, t.nombre, t.lista_autores, t.filename')
            ->join('trabajos t', 't.id_trabajo = rpt.id_trabajo')
            ->where('rpt.id_presentacion', $id_presentacion)
            ->orderBy('rpt.orden', 'ASC')
            ->get()
            ->getResultObject();
        }
        
}
