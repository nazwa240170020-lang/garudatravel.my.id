<?php

namespace Database\Seeders;

use App\Models\SiteSection;
use Illuminate\Database\Seeder;

class SiteSectionSeeder extends Seeder
{
    public function run(): void
    {
        SiteSection::updateOrCreate(['slug' => 'hotels'], [
            'title' => 'Tempat Menginap Terbaik untuk Perjalananmu',
            'subtitle' => 'Pilihan hotel rekanan Garuda dengan harga spesial untuk penumpang.',
            'is_active' => true,
            'data' => [
                'items' => [
                    [
                        'name' => 'Grand Indigo Hotel',
                        'city' => 'Jakarta',
                        'price' => 850000,
                        'rating' => 4.8,
                        'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=600&q=80',
                    ],
                    [
                        'name' => 'Aceh Bayfront Resort',
                        'city' => 'Lhokseumawe',
                        'price' => 620000,
                        'rating' => 4.6,
                        'image' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=600&q=80',
                    ],
                    [
                        'name' => 'Bali Sunset Villas',
                        'city' => 'Denpasar',
                        'price' => 1250000,
                        'rating' => 4.9,
                        'image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=600&q=80',
                    ],
                ],
            ],
        ]);

        SiteSection::updateOrCreate(['slug' => 'schedule'], [
            'title' => 'Jadwal Penerbangan Populer',
            'subtitle' => 'Cek rute dan jam terbang favorit penumpang minggu ini.',
            'is_active' => true,
            'data' => [
                'items' => [
                    [
                        'airline' => 'Garuda Indonesia',
                        'logo' => null,
                        'from' => 'CGK', 'from_city' => 'Jakarta',
                        'to' => 'DPS', 'to_city' => 'Bali',
                        'depart' => '07:30', 'arrive' => '09:15',
                        'duration' => '1h 45m',
                        'transit' => 0,
                        'price' => 1850000,
                    ],
                    [
                        'airline' => 'Garuda Indonesia',
                        'logo' => null,
                        'from' => 'CGK', 'from_city' => 'Jakarta',
                        'to' => 'SUB', 'to_city' => 'Surabaya',
                        'depart' => '09:15', 'arrive' => '10:40',
                        'duration' => '1h 25m',
                        'transit' => 0,
                        'price' => 1200000,
                    ],
                    [
                        'airline' => 'Garuda Indonesia',
                        'logo' => null,
                        'from' => 'LSW', 'from_city' => 'Lhokseumawe',
                        'to' => 'CGK', 'to_city' => 'Jakarta',
                        'depart' => '13:00', 'arrive' => '15:10',
                        'duration' => '2h 10m',
                        'transit' => 1,
                        'price' => 2150000,
                    ],
                    [
                        'airline' => 'Garuda Indonesia',
                        'logo' => null,
                        'from' => 'CGK', 'from_city' => 'Jakarta',
                        'to' => 'KNO', 'to_city' => 'Medan',
                        'depart' => '16:40', 'arrive' => '18:45',
                        'duration' => '2h 05m',
                        'transit' => 0,
                        'price' => 1750000,
                    ],
                ],
            ],
        ]);

        SiteSection::updateOrCreate(['slug' => 'testimonial'], [
            'title' => 'Apa Kata Mereka',
            'subtitle' => 'Pengalaman nyata dari penumpang yang sudah terbang bersama kami.',
            'is_active' => true,
            'data' => [
                'items' => [
                    [
                        'name' => 'Putri Anandita',
                        'role' => 'Frequent Flyer',
                        'message' => 'Proses booking-nya cepat banget dan pilihan kursinya jelas. Pelayanan di pesawat juga ramah.',
                        'avatar' => 'https://i.pravatar.cc/100?img=47',
                        'rating' => 5,
                    ],
                    [
                        'name' => 'Rangga Pratama',
                        'role' => 'Business Traveler',
                        'message' => 'Selalu pakai Garuda untuk perjalanan kerja. Tepat waktu dan kabinnya nyaman.',
                        'avatar' => 'https://i.pravatar.cc/100?img=12',
                        'rating' => 5,
                    ],
                    [
                        'name' => 'Siti Marlina',
                        'role' => 'Family Trip',
                        'message' => 'Bawa anak kecil tetap nyaman, staf bandara membantu banget waktu check-in.',
                        'avatar' => 'https://i.pravatar.cc/100?img=32',
                        'rating' => 4,
                    ],
                ],
            ],
        ]);

        SiteSection::updateOrCreate(['slug' => 'call-us'], [
            'title' => 'Ada yang bisa kami bantu?',
            'subtitle' => 'Customer service kami siap bantu soal booking, refund, atau kebutuhan khusus perjalananmu, kapan saja.',
            'is_active' => true,
            'data' => [
                'phone' => '0804-1-807-807',
                'email' => 'admin@garuda.com',
                'hours' => '24 Jam Setiap Hari',
            ],
        ]);
    }
}
