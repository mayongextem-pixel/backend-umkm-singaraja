<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Pengaturan ini mengizinkan Next.js Anda (http://localhost:3000) 
    | untuk mengirim data (Login/Delete/Post) ke Laravel.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Mengizinkan asal permintaan dari port Next.js
    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Ubah ke true agar token/session bisa terkirim dengan aman
    'supports_credentials' => true,

];