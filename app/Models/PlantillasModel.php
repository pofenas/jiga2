<?php

namespace App\Models;

class PlantillasModel extends CrudModel
{
    protected $table         = 'plantillas';
    protected $primaryKey    = 'id_plantilla';
    protected $allowedFields = ['nombre', 'filename', 'archivo', 'id_evento', 'privada'];

    public function listar(?int $id_evento = null, bool $soloPublicas = false): array
        {
        $builder = $this->db
            ->table('plantillas p')
            ->select('p.id_plantilla, p.nombre, p.filename, p.privada, p.id_evento, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = p.id_evento', 'left')
            ->orderBy('p.id_plantilla', 'ASC');

        if ($id_evento !== null)
            $builder->where('p.id_evento', $id_evento);

        if ($soloPublicas)
            $builder->where('p.privada', false);

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

    // Inserción especial con archivo binario
    public function crearConArchivo(
            string $nombre,
            string $filename,
            int $id_evento,
            bool $privada,
            string $binario
        ): int|false
        {
            $sql = 'INSERT INTO plantillas
                    (nombre, filename, id_evento, privada, archivo)
                    VALUES (?, ?, ?, ?, decode(?, \'hex\'))
                    RETURNING id_plantilla';

            $query = $this->db->query($sql, [
                $nombre,
                $filename,
                $id_evento,
                $privada,
                bin2hex($binario),
            ]);

            if (!$query)
                {
                return false;
                }

            $row = $query->getRow();

            return $row ? (int) $row->id_plantilla : false;
        }
   public function actualizarConArchivo(
            int $id,
            string $nombre,
            string $filename,
            int $id_evento,
            bool $privada,
            string $binario
        ): bool
        {
            $sql = 'UPDATE plantillas
                    SET nombre = ?,
                        filename = ?,
                        id_evento = ?,
                        privada = ?,
                        archivo = decode(?, \'hex\')
                    WHERE id_plantilla = ?';

            return (bool) $this->db->query($sql, [
                $nombre,
                $filename,
                $id_evento,
                $privada,
                bin2hex($binario),
                $id,
            ]);
        }

    public function crearSinArchivo(string $nombre, int $id_evento, bool $privada): int|false
        {
        $p   = $privada ? 'true' : 'false';
        $sql = "INSERT INTO plantillas (nombre, id_evento, privada)
                VALUES (?, ?, {$p})
                RETURNING id_plantilla";

        $query = $this->db->query($sql, [$nombre, $id_evento]);

        if (!$query) { return false; }
        $row = $query->getRow();
        return $row ? (int) $row->id_plantilla : false;
        }

    public function actualizarSinArchivo(int $id, string $nombre,
                                     int $id_evento, bool $privada): bool
        {
        $p = $privada ? 'true' : 'false';
        return (bool) $this->db->query(
            "UPDATE plantillas SET nombre = ?, id_evento = ?, privada = {$p}
            WHERE id_plantilla = ?",
            [$nombre, $id_evento, $id]
        );
        }
    public function listarUno(int $id): ?object
        {
        return $this->db->table('plantillas p')
            ->select('p.*, e.nombre AS nombre_evento')
            ->join('eventos e', 'e.id_evento = p.id_evento', 'left')
            ->where('p.id_plantilla', $id)
            ->get()->getRow();
        }

    public function actualizarCoordenadas(int $id,
        float $pos_x_titulo,  float $pos_y_titulo,
        float $pos_x_autores, float $pos_y_autores,
        int   $font_size): bool
        {
        return (bool) $this->db->table('plantillas')
            ->where('id_plantilla', $id)
            ->update([
                'pos_x_titulo'  => $pos_x_titulo,
                'pos_y_titulo'  => $pos_y_titulo,
                'pos_x_autores' => $pos_x_autores,
                'pos_y_autores' => $pos_y_autores,
                'font_size'     => $font_size,
            ]);
        }
}
