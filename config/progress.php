<?php

return [
    /*
     * CC Name untuk footer laporan
     */
    'cc_name' => env('PROGRESS_CC_NAME', 'Bapak Kabalai'),

    /*
     * Nama resmi BWS di laporan
     */
    'bws_name' => env('PROGRESS_BWS_NAME', 'BWS Sul IV KDI'),

    /*
     * Timezone untuk greeting dinamis
     */
    'timezone' => env('PROGRESS_TIMEZONE', 'Asia/Makassar'),

    /*
     * Timeout untuk operasi browser (detik)
     */
    'screenshot_timeout' => env('PROGRESS_SCREENSHOT_TIMEOUT', 30),

    /*
     * Path penyimpanan screenshot di storage
     */
    'storage_path' => 'progress',
];
