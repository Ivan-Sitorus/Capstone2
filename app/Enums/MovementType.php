<?php

namespace App\Enums;

enum MovementType: string
{
    case Sale = 'sale';
    case Purchase = 'purchase';
    case AdjustmentIncrease = 'adjustment_increase';
    case AdjustmentDecrease = 'adjustment_decrease';
    case Waste = 'waste';
    case Correction = 'correction';
}
