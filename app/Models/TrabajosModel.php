<?php

namespace App\Models;

class TrabajosModel extends CrudModel
{
    protected $table         = 'trabajos';
    protected $primaryKey    = 'id_trabajo';
    protected $allowedFields = ['nombre', 'lista_autores', 'id_categoria', 'id_usuario', 'filename'];

    public function listarDeUsuario(int $id_usuario): array
        {
        $trabajos = $this->db
            ->table('trabajos t')
            ->select('t.*, c.nombre AS nombre_categoria')
            ->join('categorias c', 'c.id_categoria = t.id_categoria', 'left')
            ->where('t.id_usuario', $id_usuario)
            ->orderBy('t.id_trabajo', 'ASC')
            ->get()
            ->getResultObject();

        foreach ($trabajos as $t)
            {
            $t->tiene_calificacion = false; // TODO: rellenar cuando exista tabla calificaciones
            }

        return $trabajos;
        }


    public function categoriasDeEvento(int $id_evento): array
        {
        return $this->db
            ->table('categorias')
            ->where('id_evento', $id_evento)
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultObject();
        }

    public function crearConPdf(
            string $nombre,
            string $listaautores,
            int $idcategoria,
            int $idusuario,
            string $filename,
            string $binario
        ): int|false
        {
            $sql = 'INSERT INTO trabajos
                    (nombre, lista_autores, id_categoria, id_usuario, filename, pdf)
                    VALUES (?, ?, ?, ?, ?, decode(?, \'hex\'))
                    RETURNING id_trabajo';

            $query = $this->db->query($sql, [
                $nombre,
                $listaautores,
                $idcategoria,
                $idusuario,
                $filename,
                bin2hex($binario),
            ]);

            if (!$query)
            {
                return false;
            }

            $row = $query->getRow();

            return $row ? (int) $row->id_trabajo : false;
        }
    public function actualizarConPdf(
            int $id,
            string $nombre,
            string $listaautores,
            int $idcategoria,
            string $filename,
            string $binario
        ): bool
        {
            $sql = 'UPDATE trabajos
                    SET nombre = ?,
                        lista_autores = ?,
                        id_categoria = ?,
                        filename = ?,
                        pdf = decode(?, \'hex\')
                    WHERE id_trabajo = ?';

            return (bool) $this->db->query($sql, [
                $nombre,
                $lista_autores,
                $idcategoria,
                $filename,
                bin2hex($binario),
                $id,
            ]);
        }

    public function actualizarSinPdf(
        int    $id,
        string $nombre,
        string $lista_autores,
        int    $id_categoria
     ): bool
        {
        return (bool) $this->db->query(
            "UPDATE trabajos
             SET nombre = ?, lista_autores = ?, id_categoria = ?
             WHERE id_trabajo = ?",
            [$nombre, $lista_autores, $id_categoria, $id]
        );
        }

    public function descargarPdf(int $id): object|null
        {
        return $this->db
            ->table('trabajos')
            ->select('filename, pdf')
            ->where('id_trabajo', $id)
            ->get()
            ->getRow();
        }
    public function miembrosComiteCientifico(int $id_evento): array
        {
        return $this->db
            ->table('usuarios u')
            ->select('u.nombre, u.email')
            ->join('rel_usuarios_roles rr', 'rr.id_usuario = u.id_usuario')
            ->join('roles r', 'r.id_rol = rr.id_rol')
            ->where('r.nombre', 'Miembro Comité Científico')
            ->where('u.id_evento', $id_evento)
            ->get()
            ->getResultObject();
        }
    public function toggleAdmitido(int $idtrabajo): bool
        {
            return (bool) $this->db
                ->table('trabajos')
                ->where('idtrabajo', $idtrabajo)
                ->update(['admitido' => new \CodeIgniter\Database\RawSql('NOT admitido')]);
        }

    
    }
