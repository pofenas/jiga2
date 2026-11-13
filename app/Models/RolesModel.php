<?php

namespace App\Models;

class RolesModel extends CrudModel
{
    protected $table         = 'roles';
    protected $primaryKey    = 'id_rol';
    protected $allowedFields = ['nombre'];
    }
