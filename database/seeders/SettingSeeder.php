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
                'receipt_title' => 'POSMine',
                'receipt_header' => 'STIE Totalwin Semarang',
                'receipt_footer' => 'Terima kasih telah berbelanja',
                'receipt_whatsapp_template' => "Terima kasih telah berbelanja di POSMine.\nStruk: (link)",
            ],
            'payment' => [
                'qris_image' => 'qris/qris-posmine.png',
                'qris_name' => 'POSMine',
            ],
        ];

        foreach ($settings as $group => $rows) {
            foreach ($rows as $key => $value) {
                Setting::set($key, $value, $group);
            }
        }
    }
}
