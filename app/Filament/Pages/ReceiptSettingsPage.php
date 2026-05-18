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
use Illuminate\Support\Str;

class ReceiptSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static string|\UnitEnum|null $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Struk & WhatsApp';

    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Pengaturan Struk & WhatsApp';
    protected string $view = 'filament.pages.receipt-settings';

    public ?array $data = [];
    public ?array $previewData = [];

    public function mount(): void
    {
        $receiptLogo = Setting::get('receipt_logo');
        $qrisImage = Setting::get('qris_image');

        $this->form->fill([
            'cafe_name' => Setting::get('cafe_name', 'W9 Cafe'),
            'cafe_address' => Setting::get('cafe_address', 'STIE Totalwin Semarang'),
            'cafe_phone' => Setting::get('cafe_phone', ''),
            // UUID-keyed array format is CRITICAL for FileUpload initial state in custom Pages
            'receipt_logo' => $receiptLogo ? [Str::uuid()->toString() => $receiptLogo] : [],
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih telah berbelanja'),
            'receipt_show_npwp' => Setting::get('receipt_show_npwp', '0') === '1',
            'receipt_npwp' => Setting::get('receipt_npwp', ''),
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template', "Halo %s,\n\nTerima kasih telah memesan:\n%s\n\nSilakan ditunggu."),
            'qris_image' => $qrisImage ? [Str::uuid()->toString() => $qrisImage] : [],
        ]);

        // previewData uses plain string values (not UUID-keyed arrays)
        $this->previewData = [
            'cafe_name' => Setting::get('cafe_name', 'W9 Cafe'),
            'cafe_address' => Setting::get('cafe_address', 'STIE Totalwin Semarang'),
            'cafe_phone' => Setting::get('cafe_phone', ''),
            'receipt_logo' => $receiptLogo,
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih telah berbelanja'),
            'receipt_show_npwp' => Setting::get('receipt_show_npwp', '0') === '1',
            'receipt_npwp' => Setting::get('receipt_npwp', ''),
            'receipt_whatsapp_template' => Setting::get('receipt_whatsapp_template', "Halo %s,\n\nTerima kasih telah memesan:\n%s\n\nSilakan ditunggu."),
            'qris_image' => $qrisImage,
        ];
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
                    ->disk('public')
                    ->directory('receipts')
                    ->maxSize(5120)
                    ->saveUploadedFileUsing(function ($file) {
                        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'png';
                        $filename = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
                        return $file->storeAs('receipts', $filename, 'public');
                    }),
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
                    ->disk('public')
                    ->directory('qris')
                    ->maxSize(5120)
                    ->saveUploadedFileUsing(function ($file) {
                        $ext = $file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'png';
                        $filename = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
                        return $file->storeAs('qris', $filename, 'public');
                    }),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // getState() triggers saveUploadedFileUsing callbacks on FileUpload fields,
        // resolving TemporaryUploadedFile objects into stored string paths.
        $state = $this->form->getState();

        foreach ($state as $field => $value) {
            // FileUpload may return UUID-keyed array from initial fill
            if (in_array($field, ['receipt_logo', 'qris_image'])) {
                if (is_array($value)) {
                    $value = $value[array_key_first($value)] ?? null;
                }
                if (empty($value)) {
                    continue;
                }
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
        $state = $this->form->getState();

        // Extract plain string paths from UUID-keyed arrays for FileUpload fields
        foreach (['receipt_logo', 'qris_image'] as $field) {
            if (is_array($state[$field] ?? null)) {
                $state[$field] = $state[$field][array_key_first($state[$field])] ?? null;
            }
        }

        $this->previewData = $state;
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
