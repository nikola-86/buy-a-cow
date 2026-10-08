<?php

declare(strict_types=1);

namespace App\Enums;

enum EventAction: string
{
    case Login = 'login';
    case Logout = 'logout';
    case Registration = 'registration';
    case ViewPage = 'view-page';
    case ButtonClick = 'button-click';
}
