<?php

namespace App\Enums;

enum BatchMode: string
{
    case Fefo = 'fefo';
    case Fifo = 'fifo';
}
