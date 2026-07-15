<?php

namespace Database\Seeders;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Facilty;
use App\Models\Flight;
use App\Models\FlightClass;
use App\Models\FlightSeat;
use App\Models\FlightSegment;
use Illuminate\Database\Seeder;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $airlines = Airline::withTrashed()->get()->keyBy('iata_code');
        $airports = Airport::withTrashed()->get()->keyBy('iata_code');
        $facilities = Facilty::withTrashed()->get()->keyBy('name');

        $rows = [
            // Garuda Indonesia (GA)
            [
                'flight_number' => 'GA-401',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(7)->setTime(8, 30)],
                    ['airport' => 'DPS', 'time' => now()->addDays(7)->setTime(11, 15)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1850000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 3800000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel']],
                ],
            ],
            [
                'flight_number' => 'GA-402',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'DPS', 'time' => now()->addDays(7)->setTime(12, 30)],
                    ['airport' => 'CGK', 'time' => now()->addDays(7)->setTime(15, 15)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1750000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 3600000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel']],
                ],
            ],
            [
                'flight_number' => 'GA-312',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(10, 15)],
                    ['airport' => 'SUB', 'time' => now()->addDays(5)->setTime(11, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1400000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 2900000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel']],
                ],
            ],
            [
                'flight_number' => 'GA-313',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'SUB', 'time' => now()->addDays(5)->setTime(13, 0)],
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(14, 30)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1400000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 2900000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel']],
                ],
            ],
            [
                'flight_number' => 'GA-820',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(8, 0)],
                    ['airport' => 'SIN', 'time' => now()->addDays(5)->setTime(10, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 2500000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat', 'Wi-Fi Penerbangan']],
                    ['class_type' => 'business', 'price' => 6500000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            [
                'flight_number' => 'GA-821',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'SIN', 'time' => now()->addDays(5)->setTime(12, 0)],
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(12, 50)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 2400000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat', 'Wi-Fi Penerbangan']],
                    ['class_type' => 'business', 'price' => 6200000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            [
                'flight_number' => 'GA-874',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(6)->setTime(23, 40)],
                    ['airport' => 'HND', 'time' => now()->addDays(7)->setTime(8, 50)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 7200000, 'total_seats' => 42, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat', 'Wi-Fi Penerbangan']],
                    ['class_type' => 'business', 'price' => 18500000, 'total_seats' => 12, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            [
                'flight_number' => 'GA-982',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(10)->setTime(1, 10)],
                    ['airport' => 'UPG', 'time' => now()->addDays(10)->setTime(4, 20)],
                    ['airport' => 'JED', 'time' => now()->addDays(10)->setTime(13, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 11200000, 'total_seats' => 42, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 28500000, 'total_seats' => 12, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            [
                'flight_number' => 'GA-502',
                'airline' => 'GA',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(4)->setTime(14, 0)],
                    ['airport' => 'BPN', 'time' => now()->addDays(4)->setTime(17, 10)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1650000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 3200000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel']],
                ],
            ],
            // Singapore Airlines (SQ)
            [
                'flight_number' => 'SQ-950',
                'airline' => 'SQ',
                'segments' => [
                    ['airport' => 'SIN', 'time' => now()->addDays(6)->setTime(6, 0)],
                    ['airport' => 'CGK', 'time' => now()->addDays(6)->setTime(6, 50)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 3100000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat', 'Wi-Fi Penerbangan']],
                    ['class_type' => 'business', 'price' => 8500000, 'total_seats' => 10, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            [
                'flight_number' => 'SQ-951',
                'airline' => 'SQ',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(6)->setTime(8, 0)],
                    ['airport' => 'SIN', 'time' => now()->addDays(6)->setTime(10, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 3100000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat', 'Wi-Fi Penerbangan']],
                    ['class_type' => 'business', 'price' => 8500000, 'total_seats' => 10, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat', 'Akses Lounge', 'Reschedule Fleksibel', 'Wi-Fi Penerbangan']],
                ],
            ],
            // Citilink (QG)
            [
                'flight_number' => 'QG-720',
                'airline' => 'QG',
                'segments' => [
                    ['airport' => 'SUB', 'time' => now()->addDays(5)->setTime(9, 0)],
                    ['airport' => 'DPS', 'time' => now()->addDays(5)->setTime(11, 0)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 950000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
            [
                'flight_number' => 'QG-804',
                'airline' => 'QG',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(4)->setTime(15, 30)],
                    ['airport' => 'YIA', 'time' => now()->addDays(4)->setTime(16, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 750000, 'total_seats' => 24, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
            [
                'flight_number' => 'QG-805',
                'airline' => 'QG',
                'segments' => [
                    ['airport' => 'YIA', 'time' => now()->addDays(4)->setTime(17, 30)],
                    ['airport' => 'CGK', 'time' => now()->addDays(4)->setTime(18, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 750000, 'total_seats' => 24, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
            // Batik Air (ID)
            [
                'flight_number' => 'ID-6311',
                'airline' => 'ID',
                'segments' => [
                    ['airport' => 'KNO', 'time' => now()->addDays(8)->setTime(14, 25)],
                    ['airport' => 'CGK', 'time' => now()->addDays(8)->setTime(16, 45)],
                    ['airport' => 'DPS', 'time' => now()->addDays(8)->setTime(19, 15)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 2100000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 4500000, 'total_seats' => 8, 'facilities' => ['Prioritas Boarding', 'Akses Lounge', 'Makanan di Pesawat']],
                ],
            ],
            [
                'flight_number' => 'ID-6500',
                'airline' => 'ID',
                'segments' => [
                    ['airport' => 'HLP', 'time' => now()->addDays(2)->setTime(7, 30)],
                    ['airport' => 'SUB', 'time' => now()->addDays(2)->setTime(9, 00)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1150000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra', 'Makanan di Pesawat']],
                    ['class_type' => 'business', 'price' => 2200000, 'total_seats' => 6, 'facilities' => ['Prioritas Boarding', 'Makanan di Pesawat']],
                ],
            ],
            // Lion Air (JT)
            [
                'flight_number' => 'JT-302',
                'airline' => 'JT',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(3)->setTime(6, 0)],
                    ['airport' => 'DPS', 'time' => now()->addDays(3)->setTime(8, 45)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1100000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
            [
                'flight_number' => 'JT-824',
                'airline' => 'JT',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(3)->setTime(11, 30)],
                    ['airport' => 'SIN', 'time' => now()->addDays(3)->setTime(14, 20)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1400000, 'total_seats' => 30, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
            // Indonesia AirAsia (AK)
            [
                'flight_number' => 'AK-380',
                'airline' => 'AK',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(14, 15)],
                    ['airport' => 'KUL', 'time' => now()->addDays(5)->setTime(17, 25)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1050000, 'total_seats' => 30, 'facilities' => []],
                ],
            ],
            [
                'flight_number' => 'AK-381',
                'airline' => 'AK',
                'segments' => [
                    ['airport' => 'KUL', 'time' => now()->addDays(5)->setTime(18, 15)],
                    ['airport' => 'CGK', 'time' => now()->addDays(5)->setTime(19, 25)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 1050000, 'total_seats' => 30, 'facilities' => []],
                ],
            ],
            // Sriwijaya Air (SJ)
            [
                'flight_number' => 'SJ-220',
                'airline' => 'SJ',
                'segments' => [
                    ['airport' => 'CGK', 'time' => now()->addDays(4)->setTime(9, 45)],
                    ['airport' => 'SRG', 'time' => now()->addDays(4)->setTime(10, 50)],
                ],
                'classes' => [
                    ['class_type' => 'economy', 'price' => 850000, 'total_seats' => 24, 'facilities' => ['Bagasi Ekstra']],
                ],
            ],
        ];

        foreach ($rows as $row) {
            $flight = Flight::withTrashed()->updateOrCreate(
                ['flight_number' => $row['flight_number']],
                [
                    'airline_id' => $airlines[$row['airline']]->id,
                    'deleted_at' => null,
                ]
            );

            foreach ($row['segments'] as $index => $segment) {
                FlightSegment::withTrashed()->updateOrCreate(
                    ['flight_id' => $flight->id, 'sequence' => $index + 1],
                    [
                        'airport_id' => $airports[$segment['airport']]->id,
                        'time' => $segment['time'],
                        'deleted_at' => null,
                    ]
                );
            }

            foreach ($row['classes'] as $classRow) {
                $class = FlightClass::withTrashed()->updateOrCreate(
                    ['flight_id' => $flight->id, 'class_type' => $classRow['class_type']],
                    [
                        'price' => $classRow['price'],
                        'total_seats' => $classRow['total_seats'],
                        'deleted_at' => null,
                    ]
                );

                if (!empty($classRow['facilities'])) {
                    $class->facilties()->sync(
                        collect($classRow['facilities'])->map(fn (string $name) => $facilities[$name]->id)->all()
                    );
                }

                if (! FlightSeat::withTrashed()->where('flight_id', $flight->id)->where('class_type', $classRow['class_type'])->exists()) {
                    $flight->generateSeats(
                        totalSeats: $classRow['total_seats'],
                        seatsPerRow: $classRow['class_type'] === 'business' ? 4 : 6,
                        classType: $classRow['class_type']
                    );
                }
            }
        }
    }
}
