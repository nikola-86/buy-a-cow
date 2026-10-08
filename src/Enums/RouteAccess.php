<?php

declare(strict_types=1);

namespace App\Enums;

enum RouteAccess
{
    case Guest;
    case Auth;
    case Admin;
}
