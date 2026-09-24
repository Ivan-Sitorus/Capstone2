<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'receipt' => [
                'receipt_title' => 'W9 Cafe',
                'receipt_header' => 'STIE Totalwin Semarang',
                'receipt_footer' => 'Terima kasih telah berbelanja',
                'receipt_whatsapp_template' => "Struk Belanja di W9 Cafe:\n(link)\n\nAbaikan Jika Tidak Membeli",
            ],
            'payment' => [
                'qris_image' => 'qris/qris-w9cafe.png',
                'qris_name' => 'W9 Cafe',
            ],
        ];

        foreach ($settings as $group => $rows) {
            foreach ($rows as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }
}
