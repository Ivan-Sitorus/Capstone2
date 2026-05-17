<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;

class ReceiptSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';

    protected static string|\UnitEnum|null $navigationGroup = 'Pengaturan';

    protected static ?string $navigationLabel = 'Struk & WhatsApp';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Pengaturan Struk & WhatsApp';

    protected string $view = 'filament.pages.receipt-settings';

    public ?array $data = [];

    public ?array $previewData = [];

    public function mount(): void
    {
        $this->form->fill([
            'cafe_name' => Setting::get('cafe_name', 'W9 Cafe'),
            'cafe_address' => Setting::get('cafe_address', 'STIE Totalwin Semarang'),
            'cafe_phone' => Setting::get('cafe_phone', ''),
            'receipt_logo' => Setting::get('receipt_logo', ''),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih telah berbelanja'),
            'receipt_show_npwp' => Setting::get('receipt_show_npwp', '0') === '1',
            'receipt_npwp' => Setting::get('receipt_npwp', ''),
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template', "Halo %s,\n\nTerima kasih telah memesan:\n%s\n\nSilakan ditunggu."),
            'qris_image' => Setting::get('qris_image', ''),
        ]);

        $this->previewData = $this->data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cafe_name')
                    ->label('Nama Cafe')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cafe_address')
                    ->label('Alamat Cafe')
                    ->required()
                    ->maxLength(500),
                TextInput::make('cafe_phone')
                    ->label('Telepon Cafe')
                    ->tel()
                    ->maxLength(20),
                FileUpload::make('receipt_logo')
                    ->label('Logo Struk')
                    ->image()
                    ->maxSize(5120)
                    ->directory('receipts'),
                Textarea::make('receipt_footer')
                    ->label('Footer Struk')
                    ->rows(3)
                    ->maxLength(500),
                Toggle::make('receipt_show_npwp')
                    ->label('Tampilkan NPWP'),
                TextInput::make('receipt_npwp')
                    ->label('NPWP')
                    ->maxLength(30),
                Textarea::make('receipt_whatsapp_template')
                    ->label('Template WhatsApp')
                    ->rows(3)
                    ->helperText('Gunakan %s untuk nama pelanggan dan %s untuk daftar pesanan.'),
                FileUpload::make('qris_image')
                    ->label('Gambar QRIS')
                    ->image()
                    ->maxSize(5120)
                    ->directory('qris'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $fields = [
            'cafe_name',
            'cafe_address',
            'cafe_phone',
            'receipt_logo',
            'receipt_footer',
            'receipt_show_npwp',
            'receipt_npwp',
            'receipt_whatsapp_template',
            'qris_image',
        ];

        foreach ($fields as $field) {
            $value = $this->data[$field] ?? '';

            if ($field === 'receipt_show_npwp') {
                $value = $value ? '1' : '0';
            }

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
        $this->previewData = $this->data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Pengaturan')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action(fn () => $this->save()),
        ];
    }
}
