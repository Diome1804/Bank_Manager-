<?php

namespace App\Enums;

enum TypeUser: string
{
    case ADMIN = 'admin';
    case CLIENT = 'client';
}