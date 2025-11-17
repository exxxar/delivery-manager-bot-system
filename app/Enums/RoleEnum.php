<?php

namespace App\Enums;

enum RoleEnum: int
{
    case USER = 0;
    case AGENT = 1;
    case SUPPLIER = 2;
    case ADMIN = 3;
    case SUPERADMIN = 4;
}
