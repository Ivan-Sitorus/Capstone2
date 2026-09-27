<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'receipt_title' => 'minePOS',
            'receipt_header' => 'STIE Totalwin Semarang',
            'receipt_footer' => 'Terima kasih telah berbelanja',
            'receipt_whatsapp_template' => "Terima kasih telah berbelanja di minePOS.\nStruk: (link)",
            'qris_image' => 'qris/qris-minepos.png',
            'qris_name' => 'minePOS',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
