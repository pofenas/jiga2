<?php

namespace App\Controllers;

class Logout extends BaseController
{
    public function getLogout()
        {
        session()->destroy();
        header('Location: ' . base_url('login'));
        exit();
        }
    }
