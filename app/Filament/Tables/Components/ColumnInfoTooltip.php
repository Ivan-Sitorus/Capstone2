<?php

namespace App\Filament\Tables\Components;

use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

use function Filament\Support\generate_icon_html;

class ColumnInfoTooltip
{
    public static function label(string $columnLabel, string $tooltipText): Htmlable
    {
        $tooltipHtml = nl2br(e($tooltipText));
        $icon = generate_icon_html(
            Heroicon::OutlinedInformationCircle,
            attributes: new ComponentAttributeBag([
                'x-tooltip' => '{ content: ' . Js::from($tooltipHtml) . ', theme: $store.theme, allowHTML: true }',
            ]),
        );

        return new HtmlString(
            '<span style="display:inline-flex;gap:4px">'
            . e($columnLabel)
            . ($icon?->toHtml() ?? '')
            . '</span>'
        );
    }
}
