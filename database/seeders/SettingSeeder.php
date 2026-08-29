<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'PT.Xseller Punya Kita',
                'type' => 'text',
            ],
            [
                'key' => 'company_name',
                'value' => 'PT.Xseller Punya Kita',
                'type' => 'text',
            ],
            [
                'key' => 'company_owner',
                'value' => 'PT.Xseller Punya Kita',
                'type' => 'text',
            ],
            [
                'key' => 'company_copyright',
                'value' => 'PT.Xseller Punya Kita Corp. Hak Cipta Dilindungi Undang-Undang.',
                'type' => 'text',
            ],
            [
                'key' => 'company_banks',
                'value' => json_encode([
                    [
                        'bank_name' => 'Bank BRI',
                        'account_number' => '806401000095564',
                        'account_name' => 'PT.Xseller Punya Kita',
                    ]
                ]),
                'type' => 'json',
            ],
            [
                'key' => 'site_description',
                'value' => 'Sebuah platform e-commerce & affiliate marketing serbaguna berbasis Laravel dan Vue 3.',
                'type' => 'textarea',
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'image',
            ],
            [
                'key' => 'whatsapp_number',
                'value' => '6281234567890',
                'type' => 'text',
            ],
            [
                'key' => 'playstore_link',
                'value' => 'https://play.google.com/store/apps/details?id=com.xseller.app',
                'type' => 'url',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
