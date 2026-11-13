<?php

namespace App\Models;

class PremiosModel extends CrudModel
{
    protected $table         = 'premios';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'nombre',
        'id_categoria',
        'id_premiado',
        'id_evento',
    ];

    public function listar(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('premios p')
            ->select("
                p.id,
                p.nombre,
                p.id_categoria,
                p.id_premiado,
                p.id_evento,
                c.nombre AS nombre_categoria,
                e.nombre AS nombre_evento,
                t.nombre AS nombre_premiado
            ")
            ->join('categorias c', 'c.id_categoria = p.id_categoria', 'left')
            ->join('eventos e', 'e.id_evento = p.id_evento', 'left')
            ->join('trabajos t', 't.id_trabajo = p.id_premiado', 'left')
            ->orderBy('p.id', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('p.id_evento', $id_evento);
            }

        return $builder->get()->getResultObject();
        }

    public function categoriasDisponibles(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('categorias')
            ->select('id_categoria, nombre, id_evento')
            ->orderBy('nombre', 'ASC');

        if ($id_evento !== null)
            {
            $builder->where('id_evento', $id_evento);
            }

        return $builder->get()->getResultObject();
        }

    public function trabajosDisponibles(?int $id_evento = null): array
        {
        $builder = $this->db
            ->table('trabajos t')
            ->select('t.id_trabajo, t.nombre, c.id_evento')
            ->join('categorias c', 'c.id_categoria = t.id_categoria', 'left')
            ->orderBy('t.nombre', 'ASC');

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

    public function buscar(int $id): ?object
        {
        return $this->db
            ->table('premios p')
            ->select('p.*, c.id_evento AS id_evento_categoria')
            ->join('categorias c', 'c.id_categoria = p.id_categoria', 'left')
            ->where('p.id', $id)
            ->get()
            ->getRow();
        }

    public function trabajosOrdenados(int $id_premio): array
        {
        $premio = $this->buscar($id_premio);

        if (!$premio)
            {
            return [];
            }

        $sql = "
            WITH notas_por_item AS
                (
                SELECT c.id_trabajo,
                    c.id_item,
                    AVG(c.nota) AS nota_media
                FROM calificaciones c
                GROUP BY c.id_trabajo, c.id_item
                )
            SELECT t.id_trabajo,
                t.nombre,
                t.lista_autores,
                ROUND(
                    SUM(n.nota_media * i.peso) / NULLIF(SUM(i.peso), 0)
                , 2) AS calificacion
            FROM trabajos t
            JOIN categorias c ON c.id_categoria = t.id_categoria
            JOIN items i ON i.id_evento = c.id_evento
            LEFT JOIN notas_por_item n
                ON n.id_trabajo = t.id_trabajo
                AND n.id_item = i.id_item
            WHERE t.id_categoria = ?
            AND t.admitido = true
            GROUP BY t.id_trabajo, t.nombre, t.lista_autores
            ORDER BY calificacion DESC NULLS LAST, t.nombre ASC
        ";

        return $this->db->query($sql, [$premio->id_categoria])
                        ->getResultObject();
        }

    public function detallesCalificaciones(int $id_premio): array
        {
        $premio = $this->buscar($id_premio);

        if (!$premio)
            {
            return [];
            }

        $sql = "
            WITH notas_por_item AS
                (
                SELECT c.id_trabajo,
                    c.id_item,
                    AVG(c.nota) AS nota_media
                FROM calificaciones c
                GROUP BY c.id_trabajo, c.id_item
                )
            SELECT t.id_trabajo,
                i.nombre AS item,
                i.peso,
                n.nota_media
            FROM trabajos t
            JOIN categorias c ON c.id_categoria = t.id_categoria
            JOIN items i ON i.id_evento = c.id_evento
            LEFT JOIN notas_por_item n
                ON n.id_trabajo = t.id_trabajo
                AND n.id_item = i.id_item
            WHERE t.id_categoria = ?
            AND t.admitido = true
            ORDER BY t.id_trabajo, i.id_item
        ";

        $filas = $this->db->query($sql, [$premio->id_categoria])
                        ->getResultObject();

        $detalles = [];

        foreach ($filas as $fila)
            {
            $detalles[$fila->id_trabajo][] = [
                'item' => $fila->item,
                'peso' => (float) $fila->peso,
                'nota' => $fila->nota_media !== null
                    ? round((float) $fila->nota_media, 2)
                    : null,
            ];
            }

        return $detalles;
        }
    public function trabajoValidoParaPremio(
            int $id_trabajo,
            int $id_categoria,
            int $id_evento
        ): bool
            {
            return (bool) $this->db
                ->table('trabajos t')
                ->join('categorias c', 'c.id_categoria = t.id_categoria')
                ->where('t.id_trabajo', $id_trabajo)
                ->where('t.id_categoria', $id_categoria)
                ->where('c.id_evento', $id_evento)
                ->where('t.admitido', true)
                ->countAllResults();
            }

    public function asignar(int $id_premio, ?int $id_trabajo): bool
        {
        return (bool) $this->db
            ->table('premios')
            ->where('id', $id_premio)
            ->update(['id_premiado' => $id_trabajo]);
        }

        }