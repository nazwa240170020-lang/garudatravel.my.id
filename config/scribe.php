<?php

return [

    'theme' => 'default',

    'title' => 'Garuda Flight Booking API Documentation',

    'description' => 'Dokumentasi API untuk Sistem Pemesanan Tiket Garuda Travel.',

    'base_url' => null,

    'routes' => [
        [
            /*
             * Tentukan route mana yang akan didokumentasikan oleh Scribe.
             */
            'match' => [
                'prefixes' => ['*'],
                'domains' => ['*'],
            ],

            /*
             * Kecualikan route admin Filament, internal Laravel, dan autentikasi dasar.
             */
            'exclude' => [
                'admin*',
                'sanctum/*',
                'livewire/*',
                '_ignition/*',
            ],

            'apply' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],

                /*
                 * Jika Scribe membuat respons dummy melalui Response Calls.
                 */
                'response_calls' => [
                    'methods' => ['GET'],
                    'config' => [
                        'app.env' => 'documentation',
                    ],
                ],
            ],
        ],
    ],

    /*
     * Tipe output dokumentasi.
     * 'static' akan menghasilkan file HTML di public/docs/index.html.
     * 'laravel' akan menyajikan dokumentasi secara dinamis via route /docs.
     */
    'type' => 'static',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'middleware' => [],
    ],

    'try_it_out' => [
        'enabled' => true,
    ],

    'logo' => false,

    'last_updated_str' => 'Terakhir diperbarui pada: {date}',

    'fractal' => [
        'serializer' => null,
    ],

    'routeMatcher' => \Knuckles\Scribe\Matching\RouteMatcher::class,

    'database_connections_to_transact' => [config('database.default')],

];
