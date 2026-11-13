<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'noreply@almansa.ovh';
    public string $fromName   = 'JIGA 2';
    public string $protocol   = 'smtp';
    public string $SMTPHost   = 'smtp.resend.com';
    public int    $SMTPPort   = 465;
    public string $SMTPCrypto = 'ssl';
    public string $SMTPUser   = 'resend';
    public string $SMTPPass   = '';       // se sobreescribe desde .env
    public string $mailType   = 'html';
    public string $charset    = 'UTF-8';
    }
