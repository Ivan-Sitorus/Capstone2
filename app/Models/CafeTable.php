<?php

namespace App\Models;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CafeTable extends Model
{
    use SoftDeletes;

    protected $table = 'cafe_tables';

    protected $fillable = ['table_number', 'qr_token'];

    protected function casts(): array
    {
        return [];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    public function getQrUrlAttribute(): string
    {
        return route('customer.identity', ['table' => $this->qr_token]);
    }

    public function getQrSvgAttribute(): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel' => EccLevel::L,
            'outputBase64' => false,
        ]);

        return (new QRCode($options))->render($this->qr_url);
    }

    public function getQrSvgDataUriAttribute(): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->qr_svg);
    }

    public function getQrPngDataUriAttribute(): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'eccLevel' => EccLevel::L,
            'outputBase64' => true,
            'scale' => 10,
        ]);

        return 'data:image/png;base64,'.(new QRCode($options))->render($this->qr_url);
    }

    public function generatePngDownload(): string
    {
        $options = new QROptions([
            'outputType' => QROutputInterface::GDIMAGE_PNG,
            'eccLevel' => EccLevel::L,
            'scale' => 10,
        ]);

        $raw = (new QRCode($options))->render($this->qr_url);

        // render() returns data:image/png;base64,XXXX — strip the prefix
        $base64 = substr($raw, strpos($raw, ',') + 1);

        return base64_decode($base64);
    }

    protected static function booted(): void
    {
        static::creating(function (CafeTable $table) {
            if (empty($table->qr_token)) {
                $table->qr_token = (string) Str::uuid();
            }
        });
    }
}
