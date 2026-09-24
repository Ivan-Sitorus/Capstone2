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

    public ?array $data = [];
    public ?array $previewData = [];
    public ?string $whatsappPreview = null;

    public function getView(): string
    {
        return 'filament.pages.receipt-settings';
    }

    public function mount(): void
    {
        $this->form->fill([
            'receipt_title' => Setting::get('receipt_title') ?? '',
            'receipt_header' => Setting::get('receipt_header') ?? '',
            'receipt_footer' => Setting::get('receipt_footer') ?? '',
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template') ?? '',
        ]);

        $this->previewData = [
            'receipt_title' => Setting::get('receipt_title') ?? '',
            'receipt_header' => Setting::get('receipt_header') ?? '',
            'receipt_footer' => Setting::get('receipt_footer') ?? '',
        ];

        $this->whatsappPreview = Setting::get('receipt_whatsapp_template') ?? '';
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
                    ->maxLength(255)
                    ->helperText('Bisa diisi multi baris (Enter untuk baris baru).'),
                Textarea::make('receipt_footer')
                    ->label('Footer')
                    ->rows(3)
                    ->maxLength(255)
                    ->helperText('Bisa diisi multi baris (Enter untuk baris baru).'),
                Textarea::make('receipt_whatsapp_template')
                    ->label('Template WhatsApp')
                    ->rows(3)
                    ->maxLength(255)
                    ->helperText(new \Illuminate\Support\HtmlString('Bisa diisi multi baris (Enter untuk baris baru).<br>Gunakan "(link)" untuk menempatkan tautan struk.')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $field => $value) {
            Setting::set($field, $value, 'receipt');
        }

        Cache::flush();

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();
    }

    public function refreshReceiptPreview(): void
    {
        $state = $this->form->getState();

        $this->previewData = [
            'receipt_title' => $state['receipt_title'] ?? '',
            'receipt_header' => $state['receipt_header'] ?? '',
            'receipt_footer' => $state['receipt_footer'] ?? '',
        ];
    }

    public function refreshWhatsappPreview(): void
    {
        $state = $this->form->getState();

        $this->whatsappPreview = $state['receipt_whatsapp_template'] ?? '';
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
