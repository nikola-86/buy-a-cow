<?php

declare(strict_types=1);

namespace App\Enums;

enum EventTarget: string
{
    case PageA = 'page_a';
    case PageB = 'page_b';
    case BuyCow = 'buy_cow';
    case Download = 'download';
}
