<?php

namespace App\Models;

use CodeIgniter\Model;

class CalificacionesModel extends Model
{
    protected $table         = 'calificaciones';
    protected $primaryKey    = 'id_calificacion';
    protected $returnType    = 'object';
    protected $allowedFields = ['id_usuario', 'id_trabajo', 'id_item', 'nota'];

    /**
     * Trabajos del evento con las notas del calificador (solo las suyas).
     * El evento se deduce por usuarios.id_evento a través de id_usuario del trabajo.
     */
    public function trabajosConMisCalificaciones(int $id_evento, int $id_usuario_calificador): array
        {
        // Items del evento
        $items = $this->db->table('items')
            ->where('id_evento', $id_evento)
            ->get()->getResultObject();

        // Trabajos del evento (via categoria → id_evento)
        $trabajos = $this->db->table('trabajos t')
            ->select('t.id_trabajo, t.nombre, t.lista_autores, t.admitido, cat.nombre AS categoria')
            ->join('categorias cat', 'cat.id_categoria = t.id_categoria')
            ->where('cat.id_evento', $id_evento)
            ->get()->getResultObject();
        // Calificaciones del calificador para este evento
        $cals = $this->db->table('calificaciones c')
            ->select('c.id_trabajo, c.id_item, c.nota')
            ->join('items i', 'i.id_item = c.id_item')
            ->where('c.id_usuario', $id_usuario_calificador)
            ->where('i.id_evento', $id_evento)
            ->get()->getResultArray();

        // Indexar por [id_trabajo][id_item] => nota
        $index = [];
        foreach ($cals as $c)
            $index[$c['id_trabajo']][$c['id_item']] = $c['nota'];

        // Montar resultado con estado de tres valores
        $total = count($items);
        foreach ($trabajos as $t)
            {
            $t->items    = [];
            $con_nota    = 0;

            foreach ($items as $item)
                {
                $nota = $index[$t->id_trabajo][$item->id_item] ?? null;
                $t->items[] = (object)[
                    'id_item' => $item->id_item,
                    'nombre'  => $item->nombre,
                    'nota'    => $nota,
                ];
                if ($nota !== null) $con_nota++;
                }

            $t->estado = match(true)
                {
                $con_nota === 0      => 'pendiente',
                $con_nota === $total => 'completo',
                default              => 'parcial',
                };
            }

        return [$trabajos, $items];
        }


    public function guardarCalificaciones(int $id_usuario, int $id_trabajo, array $notas): void
        {
        foreach ($notas as $id_item => $nota)
            {
            // Si viene vacío, guardar null (o no insertar si no existe)
            $nota_valor = ($nota !== '' && $nota !== null)
                ? max(0, min(10, (float) $nota))
                : null;

            $existe = $this->db->table('calificaciones')
                ->where('id_usuario', $id_usuario)
                ->where('id_trabajo', $id_trabajo)
                ->where('id_item',    (int) $id_item)
                ->countAllResults();

            if ($existe)
                {
                $this->db->table('calificaciones')
                    ->where('id_usuario', $id_usuario)
                    ->where('id_trabajo', $id_trabajo)
                    ->where('id_item',    (int) $id_item)
                    ->update(['nota' => $nota_valor]);
                }
            else if ($nota_valor !== null)
                {
                // Solo insertar si hay nota — no crear registros vacíos
                $this->db->table('calificaciones')->insert([
                    'id_usuario' => $id_usuario,
                    'id_trabajo' => $id_trabajo,
                    'id_item'    => (int) $id_item,
                    'nota'       => $nota_valor,
                ]);
                }
            }
        }
    public function todosSinFiltroEvento(int $id_usuario_calificador): array
        {
        $db = \Config\Database::connect();

        $items = $db->table('items')->get()->getResultObject();

        $trabajos = $this->db->table('trabajos t')
            ->select('t.id_trabajo, t.nombre, t.lista_autores, t.admitido, cat.nombre AS categoria, e.nombre AS evento')
            ->join('categorias cat', 'cat.id_categoria = t.id_categoria')
            ->join('eventos e', 'e.id_evento = cat.id_evento')
            ->get()->getResultObject();

        $cals = $db->table('calificaciones c')
            ->select('c.id_trabajo, c.id_item, c.nota')
            ->where('c.id_usuario', $id_usuario_calificador)
            ->get()->getResultArray();

        $index = [];
        foreach ($cals as $c)
            $index[$c['id_trabajo']][$c['id_item']] = $c['nota'];

        $total = count($items);
        foreach ($trabajos as $t)
            {
            $t->items = [];
            $con_nota = 0;

            foreach ($items as $item)
                {
                $nota = $index[$t->id_trabajo][$item->id_item] ?? null;
                $t->items[] = (object)[
                    'id_item' => $item->id_item,
                    'nombre'  => $item->nombre,
                    'nota'    => $nota,
                ];
                if ($nota !== null) $con_nota++;
                }

            $t->estado = match(true)
                {
                $con_nota === 0      => 'pendiente',
                $con_nota === $total => 'completo',
                default              => 'parcial',
                };
            }

        return [$trabajos, $items];
        }

}
