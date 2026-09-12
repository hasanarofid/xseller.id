<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roProducts = [
            [
                'type' => 'ro',
                'name' => 'GroVit',
                'price' => 125000,
                'quantity' => 2,
                'points' => 1,
                'image' => '/images/products/grovit.jpeg',
                'description' => 'Nutrisi anak tinggi kalsium & DHA GroVit rasa jeruk (Dapat 2 botol per Paket RO).',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Lambung Gembira',
                'price' => 125000,
                'quantity' => 2,
                'points' => 1,
                'image' => '/images/products/lambung_gembira.jpeg',
                'description' => 'Minuman serbuk sereal dengan pisang almond Nuhsa Lambung Gembira (Dapat 2 per Paket RO).',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Oxibumin',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'image' => '/images/products/oxibumin.jpeg',
                'description' => '90% Asli Ekstrak Ikan Gabus Channa Striata Nuhsa Oxibumin (Dapat 1 botol per Paket RO).',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => 'Kopi Cap Sanghai',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'image' => '/images/products/sanghai.jpeg',
                'description' => 'Minuman serbuk kopi gula krimer dengan ginseng Nuhsa Kopi Cap Sanghai (Dapat 1 box per Paket RO).',
                'is_active' => true,
            ],
            [
                'type' => 'ro',
                'name' => '3IN1 HAZAPRO',
                'price' => 125000,
                'quantity' => 1,
                'points' => 1,
                'image' => '/images/products/hazapro.jpeg',
                'description' => 'Herbal 3in1 Hazapro 60 Kapsul kualitas terbaik (Dapat 1 botol per Paket RO).',
                'is_active' => true,
            ],
        ];

        $poProducts = [
            [
                'type' => 'po',
                'name' => 'HQ Herba Queena (Star Seller)',
                'price' => 550000,
                'quantity' => 3,
                'points' => 2,
                'image' => '/images/products/hq_herba_queena.jpeg',
                'description' => 'Konsentrat Minuman Fermentasi HQ Herba Queena (Dapat 3 botol untuk Paket Star Seller Rp 550.000).',
                'is_active' => true,
            ],
            [
                'type' => 'po',
                'name' => 'HQ Herba Queena (Affiliate)',
                'price' => 2100000,
                'quantity' => 12,
                'points' => 8,
                'image' => '/images/products/hq_herba_queena.jpeg',
                'description' => 'Konsentrat Minuman Fermentasi HQ Herba Queena (Dapat 12 botol untuk Paket Affiliate Rp 2.100.000).',
                'is_active' => true,
            ],
            [
                'type' => 'po',
                'name' => 'Romero Coffee',
                'price' => 550000,
                'quantity' => 3,
                'points' => 2,
                'image' => '/images/products/romero_coffee.jpeg',
                'description' => 'Minuman kopi dengan krimer Romero Coffee (Dapat 3 box untuk PO Rp 550.000).',
                'is_active' => true,
            ],
        ];

        Schema::disableForeignKeyConstraints();
        Product::query()->delete();
        Schema::enableForeignKeyConstraints();

        foreach ($roProducts as $item) {
            Product::create($item);
        }

        foreach ($poProducts as $item) {
            Product::create($item);
        }
    }
}
