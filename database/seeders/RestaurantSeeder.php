<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\OpeningHour;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');
        $merchants = User::where('role', 'merchant')->get()->values();
        $users = User::where('role', 'user')->where('status', 'active')->get()->values();

        $restaurantsData = [
            [
                'name' => 'Soto Ayam Bangkong',
                'description' => 'Soto ayam khas Semarang legendaris dengan kuah bening gurih dan sate pelengkap lezat.',
                'address' => 'Jl. Brigjen Katamso No.1, Peterongan, Semarang Selatan',
                'phone' => '+62248412283',
                'lat' => -6.9934500,
                'lng' => 110.4289000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Makanan Tradisional', 'Nasi & Lauk'],
            ],
            [
                'name' => 'Loenpia Mbok Lien',
                'description' => 'Lumpia khas Semarang basah dan goreng dengan isian rebung segar dan kepiting.',
                'address' => 'Jl. Pemuda No.1, Pandansari, Semarang Tengah',
                'phone' => '+62243544567',
                'lat' => -6.9745000,
                'lng' => 110.4182000,
                'price_level' => 2,
                'status' => 'active',
                'categories' => ['Makanan Tradisional', 'Camilan & Dessert'],
            ],
            [
                'name' => 'Kopi Spiegel Bar & Bistro',
                'description' => 'Kafe berarsitektur kolonial di kawasan Kota Lama, menyajikan specialty coffee dan artisan pastries.',
                'address' => 'Jl. Letjen Suprapto No.34, Kota Lama, Semarang',
                'phone' => '+6281228001900',
                'lat' => -6.9682000,
                'lng' => 110.4278000,
                'price_level' => 3,
                'status' => 'active',
                'categories' => ['Kopi & Teh', 'Camilan & Dessert'],
            ],
            [
                'name' => 'Nasi Gandul Pak Memet',
                'description' => 'Nasi gandul khas Pati berkuah santan gurih manis dengan aneka pilihan jeroan dan daging sapi.',
                'address' => 'Jl. Dokter Cipto No.12, Bugangan, Semarang Timur',
                'phone' => '+6281325447890',
                'lat' => -6.9821000,
                'lng' => 110.4352000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Nasi & Lauk', 'Makanan Tradisional'],
            ],
            [
                'name' => 'Toko Oen Semarang',
                'description' => 'Restoran kolonial tempo dulu sejak 1936, terkenal dengan es krim buatan tangan dan bistik sapi.',
                'address' => 'Jl. Pemuda No.52, Bangunharjo, Semarang Tengah',
                'phone' => '+62243541683',
                'lat' => -6.9768000,
                'lng' => 110.4195000,
                'price_level' => 3,
                'status' => 'active',
                'categories' => ['Camilan & Dessert', 'Makanan Tradisional'],
            ],
            [
                'name' => 'Bakso Doa Ibu Simpang Lima',
                'description' => 'Bakso sapi asli dengan tetelan melimpah dan kuah kaldu segar di jantung Simpang Lima.',
                'address' => 'Jl. Sompok Lama No.65, Peterongan, Semarang Selatan',
                'phone' => '+628123456781',
                'lat' => -6.9921000,
                'lng' => 110.4320000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Bakso & Mie'],
            ],
            [
                'name' => 'Ayam Goreng Kampung Kali',
                'description' => 'Ayam goreng gurih empuk khas Semarang dengan sambal terasi matang dan lalapan segar.',
                'address' => 'Jl. DI Panjaitan No.45, Miroto, Semarang Tengah',
                'phone' => '+62243516543',
                'lat' => -6.9834000,
                'lng' => 110.4221000,
                'price_level' => 2,
                'status' => 'active',
                'categories' => ['Nasi & Lauk'],
            ],
            [
                'name' => 'Kedai Kopi Taru Tembalang',
                'description' => 'Tempat nongkrong santai favorit mahasiswa Undip dengan aneka manual brew dan cemilan.',
                'address' => 'Jl. Prof. Soedarto No.88, Tembalang, Semarang',
                'phone' => '+628198765432',
                'lat' => -7.0512000,
                'lng' => 110.4398000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Kopi & Teh', 'Aneka Minuman'],
            ],
            [
                'name' => 'Babat Gongso Pak Karmin Mberok',
                'description' => 'Nasi goreng babat dan babat gongso bumbu kecap pedas gurih legendaris dekat Jembatan Berok.',
                'address' => 'Jl. Pemuda, Bandarharjo, Semarang Utara',
                'phone' => '+628139012345',
                'lat' => -6.9712000,
                'lng' => 110.4265000,
                'price_level' => 2,
                'status' => 'active',
                'categories' => ['Makanan Tradisional', 'Nasi & Lauk'],
            ],
            [
                'name' => 'Mie Kopyok Pak Dhuwur',
                'description' => 'Mie kopyok segar dengan kuah bawang putih, lontong, tauge, tahu, dan kerupuk gendar.',
                'address' => 'Jl. Tanjung No.18A, Pandansari, Semarang Tengah',
                'phone' => '+628564123987',
                'lat' => -6.9789000,
                'lng' => 110.4143000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Bakso & Mie', 'Makanan Tradisional'],
            ],
            [
                'name' => 'Kampoeng Laut Seafood & Resto',
                'description' => 'Restoran apung keluarga dengan aneka olahan kepiting, udang, dan ikan laut segar.',
                'address' => 'Komplek Puri Maerokoco, Tawangmas, Semarang Barat',
                'phone' => '+62247617289',
                'lat' => -6.9587000,
                'lng' => 110.3892000,
                'price_level' => 3,
                'status' => 'active',
                'categories' => ['Seafood'],
            ],
            [
                'name' => 'Tahu Gimbal Pak Edy Pleburan',
                'description' => 'Tahu gimbal gurih dengan bumbu kacang petis udang kental, kol segar, dan gimbal udang renyah.',
                'address' => 'Jl. Menteri Supeno, Mugassari, Semarang Selatan',
                'phone' => '+628122908765',
                'lat' => -6.9912000,
                'lng' => 110.4201000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Makanan Tradisional'],
            ],
            [
                'name' => 'Pesta Keboen Restoran',
                'description' => 'Nuansa tempo doeloe dengan sajian masakan nusantara dan minuman herbal tradisional.',
                'address' => 'Jl. Veteran No.29, Lempongsari, Gajahmungkur, Semarang',
                'phone' => '+62248310620',
                'lat' => -6.9978000,
                'lng' => 110.4156000,
                'price_level' => 3,
                'status' => 'active',
                'categories' => ['Makanan Tradisional', 'Nasi & Lauk'],
            ],
            [
                'name' => 'Burger Bros Tembalang',
                'description' => 'Burger artisan dengan saus rahasia dan daging sapi premium pilihan.',
                'address' => 'Jl. Banjarsari No.12, Tembalang, Semarang',
                'phone' => '+628122334455',
                'lat' => -7.0545000,
                'lng' => 110.4412000,
                'price_level' => 2,
                'status' => 'active',
                'categories' => ['Cepat Saji'],
            ],
            [
                'name' => 'Es Puter Cong Lik',
                'description' => 'Es puter tradisional dengan varian rasa kelapa muda, durian, alpukat, dan cokelat.',
                'address' => 'Jl. KH Ahmad Dahlan No.11, Karangkidul, Semarang Tengah',
                'phone' => '+628132512345',
                'lat' => -6.9898000,
                'lng' => 110.4245000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Camilan & Dessert', 'Aneka Minuman'],
            ],
            [
                'name' => 'Warung Steak & Shake Imam Bonjol',
                'description' => 'Steak renyah murah meriah saus lezat favorit keluarga dan mahasiswa.',
                'address' => 'Jl. Imam Bonjol No.184, Sekayu, Semarang Tengah',
                'phone' => '+62243556789',
                'lat' => -6.9792000,
                'lng' => 110.4123000,
                'price_level' => 1,
                'status' => 'active',
                'categories' => ['Cepat Saji'],
            ],
            // 2 Pending
            [
                'name' => 'Waroeng Nasi Bakar Pandanaran',
                'description' => 'Aneka nasi bakar rempah cumi dan ayam suwir aroma kemangi wangi.',
                'address' => 'Jl. Pandanaran No.90, Pekunden, Semarang',
                'phone' => '+628198712345',
                'lat' => -6.9876000,
                'lng' => 110.4167000,
                'price_level' => 1,
                'status' => 'pending',
                'categories' => ['Nasi & Lauk'],
            ],
            [
                'name' => 'D’Boba Bliss Pleburan',
                'description' => 'Minuman brown sugar milk tea dengan boba kenyal lembut.',
                'address' => 'Jl. Pleburan Barat No.5, Semarang Selatan',
                'phone' => '+628176543210',
                'lat' => -6.9945000,
                'lng' => 110.4234000,
                'price_level' => 1,
                'status' => 'pending',
                'categories' => ['Aneka Minuman'],
            ],
            // 2 Banned
            [
                'name' => 'Kedai Kopi Gelap Gulita',
                'description' => 'Kedai kopi tanpa izin operasional resmi.',
                'address' => 'Jl. Gang Buntu No.99, Semarang',
                'phone' => null,
                'lat' => -6.9800000,
                'lng' => 110.4200000,
                'price_level' => 1,
                'status' => 'banned',
                'status_reason' => 'Toko melanggar standar higienis dan tidak memiliki izin usaha.',
                'categories' => ['Kopi & Teh'],
            ],
            [
                'name' => 'Warung Fiktif Makanan',
                'description' => 'Akun restoran palsu terdeteksi kecurangan.',
                'address' => 'Jl. Palsu No.0, Semarang',
                'phone' => null,
                'lat' => -6.9900000,
                'lng' => 110.4300000,
                'price_level' => 1,
                'status' => 'banned',
                'status_reason' => 'Akun toko terindikasi manipulasi ulasan dan data palsu.',
                'categories' => ['Cepat Saji'],
            ],
        ];

        foreach ($restaurantsData as $index => $rData) {
            $merchant = $merchants[$index] ?? $merchants->first();

            $restaurant = Restaurant::firstOrCreate(
                ['owner_id' => $merchant->id],
                [
                    'name' => $rData['name'],
                    'description' => $rData['description'],
                    'address' => $rData['address'],
                    'phone' => $rData['phone'],
                    'lat' => $rData['lat'],
                    'lng' => $rData['lng'],
                    'price_level' => $rData['price_level'],
                    'status' => $rData['status'],
                    'status_reason' => $rData['status_reason'] ?? null,
                    'cover_path' => null,
                    'rating_avg' => 0.00,
                    'rating_count' => 0,
                    'timezone' => 'Asia/Jakarta',
                ]
            );

            // Categories
            $catIds = [];
            foreach ($rData['categories'] as $catName) {
                if (isset($categories[$catName])) {
                    $catIds[] = $categories[$catName]->id;
                }
            }
            $restaurant->categories()->sync($catIds);

            // Opening Hours (Sunday=0 to Saturday=6)
            for ($day = 0; $day <= 6; $day++) {
                OpeningHour::firstOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'open_time' => '08:00:00',
                        'close_time' => '21:30:00',
                        'is_closed' => false,
                    ]
                );
            }

            // Menu Items (5 to 6 items)
            $sampleMenus = [
                ['group' => 'Makanan Utama', 'name' => 'Menu Spesial ' . $restaurant->name, 'price' => 25000, 'desc' => 'Porsi lengkap dengan racikan bumbu khas.'],
                ['group' => 'Makanan Utama', 'name' => 'Porsi Jumbo Hemat', 'price' => 32000, 'desc' => 'Porsi lebih banyak cocok untuk yang sangat lapar.'],
                ['group' => 'Makanan Utama', 'name' => 'Menu Komplit Telur & Kerupuk', 'price' => 28000, 'desc' => 'Dilengkapi lauk telur asin dan kerupuk.'],
                ['group' => 'Minuman', 'name' => 'Es Teh Manis Segar', 'price' => 5000, 'desc' => 'Teh melati wangi dingin.'],
                ['group' => 'Minuman', 'name' => 'Es Jeruk Peras Asli', 'price' => 8000, 'desc' => 'Jeruk peras murni kaya vitamin C.'],
                ['group' => 'Camilan', 'name' => 'Tempe Mendoan Hangat (3 pcs)', 'price' => 12000, 'desc' => 'Digoreng dadakan disajikan dengan sambal kecap pedas.'],
            ];

            foreach ($sampleMenus as $m) {
                MenuItem::firstOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'name' => $m['name'],
                    ],
                    [
                        'group_name' => $m['group'],
                        'description' => $m['desc'],
                        'price' => $m['price'],
                        'photo_path' => null,
                        'is_available' => true,
                    ]
                );
            }

            // Add reviews for active restaurants
            if ($restaurant->status === 'active') {
                $reviewSamples = [
                    ['rating' => 5, 'comment' => 'Makanannya enak sekali, tempat bersih dan pelayanan cepat ramah.'],
                    ['rating' => 4, 'comment' => 'Porsi pas, rasanya mantap. Selalu jadi pilihan saat ke Semarang.'],
                    ['rating' => 5, 'comment' => 'Salah satu kuliner wajib dicoba! Sambalnya luar biasa nikmat.'],
                ];

                foreach ($reviewSamples as $rIndex => $rev) {
                    $reviewUser = $users[$rIndex % count($users)];
                    Review::firstOrCreate(
                        [
                            'restaurant_id' => $restaurant->id,
                            'user_id' => $reviewUser->id,
                        ],
                        [
                            'rating' => $rev['rating'],
                            'comment' => $rev['comment'],
                        ]
                    );
                }
            }
        }
    }
}
