<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Video Compression Settings
    |--------------------------------------------------------------------------
    |
    | Configure video compression behavior for uploaded challenge videos.
    |
    */

    'compression' => [
        // Enable or disable automatic video compression
        'enabled' => env('VIDEO_COMPRESSION_ENABLED', true),

        // Target file size in MB (videos larger than this will be compressed)
        'target_size_mb' => env('VIDEO_TARGET_SIZE_MB', 10),

        // Maximum resolution for compressed videos
        'max_width' => env('VIDEO_MAX_WIDTH', 1280),
        'max_height' => env('VIDEO_MAX_HEIGHT', 720),

        // FFmpeg encoding settings
        'video_codec' => env('VIDEO_CODEC', 'libx264'),
        'crf' => env('VIDEO_CRF', 28), // Quality (lower = better quality, 18-28 recommended)
        'preset' => env('VIDEO_PRESET', 'fast'), // fast, medium, slow

        // Audio settings
        'audio_codec' => env('AUDIO_CODEC', 'aac'),
        'audio_bitrate' => env('AUDIO_BITRATE', '128k'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    |
    | Maximum file sizes for uploads (in MB)
    |
    */

    'max_upload_size' => [
        'image' => env('MAX_IMAGE_SIZE_MB', 5),
        'video' => env('MAX_VIDEO_SIZE_MB', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Client Upload Multiplier
    |--------------------------------------------------------------------------
    |
    | Coefficient multiplicateur pour la limite d'upload côté client.
    | Exemple : Si MAX_VIDEO_SIZE = 15MB et multiplier = 2, le client peut
    | uploader jusqu'à 30MB, sachant que le serveur compressera à 15MB max.
    |
    */

    'client_upload_multiplier' => env('VIDEO_CLIENT_MULTIPLIER', 2.0),

];
