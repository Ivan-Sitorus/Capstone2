<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use UnitEnum;

class ReceiptSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedReceiptPercent;
    protected static string | UnitEnum | null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Struk & WhatsApp';

    protected static ?int $navigationSort = 5;
    protected static ?string $title = 'Pengaturan Struk & WhatsApp';
    protected string $view = 'filament.pages.receipt-settings';

    public ?array $data = [];
    public ?array $previewData = [];

    public function mount(): void
    {
        $this->form->fill([
            'receipt_title' => Setting::get('receipt_title', Setting::get('cafe_name', 'W9 Cafe')),
            'receipt_header' => Setting::get('receipt_header', implode("\n", array_filter([
                Setting::get('cafe_address', 'STIE Totalwin Semarang'),
                Setting::get('cafe_phone', ''),
            ]))),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih telah berbelanja'),
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template', "Struk Belanja di W9 Cafe:\n(link)\n\nAbaikan Jika Tidak Membeli"),
        ]);

        $this->previewData = [
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template', "Struk Belanja di W9 Cafe:\n(link)\n\nAbaikan Jika Tidak Membeli"),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('receipt_title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Textarea::make('receipt_header')
                    ->label('Header')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Bisa diisi multi baris (Enter untuk baris baru).'),
                Textarea::make('receipt_footer')
                    ->label('Footer')
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Bisa diisi multi baris (Enter untuk baris baru).'),
                Textarea::make('receipt_whatsapp_template')
                    ->label('Template WhatsApp')
                    ->rows(3)
                    ->helperText(new \Illuminate\Support\HtmlString('Bisa diisi multi baris (Enter untuk baris baru).<br>Gunakan "(link)" untuk menempatkan tautan struk.')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $field => $value) {
            Setting::set($field, $value);
        }

        Cache::flush();

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    public function refreshPreview(): void
    {
        $this->previewData = $this->form->getState();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon(Heroicon::OutlinedCheck)
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }
}
