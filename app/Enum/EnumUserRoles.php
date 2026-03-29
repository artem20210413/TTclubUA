<?php

namespace App\Enum;

enum EnumUserRoles: string
{

    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case USER = 'user';
    case TESTER = 'tester';
    case TTOWNER = 'TTowner';

}
