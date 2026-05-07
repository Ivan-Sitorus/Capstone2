<?php

namespace App\Models;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    protected $table = 'cafe_tables';

    protected $fillable = ['table_number', 'qr_code', 'is_available'];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function getQrCodeUrlAttribute(): string
    {
        return route('customer.identitas', ['table' => $this->table_number]);
    }

    public function getQrCodeSvgAttribute(): string
    {
        $options = new QROptions([
            'outputType'  => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel'    => QRCode::ECC_L,
            'scale'       => 5,
            'imageBase64' => false,
        ]);

        return (new QRCode($options))->render($this->qr_code);
    }

    public function getQrCodeSvgDataUriAttribute(): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($this->qr_code_svg);
    }

    protected static function booted(): void
    {
        static::creating(function (CafeTable $table) {
            if (empty($table->qr_code)) {
                $table->qr_code = route('customer.identitas', ['table' => $table->table_number]);
            }
        });

        static::updating(function (CafeTable $table) {
            if ($table->isDirty('table_number')) {
                $table->qr_code = route('customer.identitas', ['table' => $table->table_number]);
            }
        });
    }
}
