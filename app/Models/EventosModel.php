<?php

namespace App\Models;

class EventosModel extends CrudModel
{
    protected $table         = 'eventos';
    protected $primaryKey    = 'id_evento';
    protected $allowedFields = ['nombre'];
    }
