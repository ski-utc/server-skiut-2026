<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class VideoCompressionService
{
    /**
     * Path to FFmpeg binary
     */
    private ?string $ffmpegPath = null;

    /**
     * Constructor - find FFmpeg on instantiation
     */
    public function __construct()
    {
        $this->ffmpegPath = $this->findFFmpeg();
    }

    /**
     * Check if FFmpeg is available
     */
    public function isAvailable(): bool
    {
        return config('video.compression.enabled', true) && $this->ffmpegPath !== null;
    }

    /**
     * Get target file size for compression in bytes
     */
    private function getTargetSize(): int
    {
        return config('video.compression.target_size_mb', 10) * 1024 * 1024;
    }

    /**
     * Compress a video file to meet a target size requirement
     *
     * @param string $videoPath Full path to the video file
     * @param int|null $maxSize Maximum size in bytes (null = use config)
     * @return array ['success' => bool, 'originalSize' => int, 'compressedSize' => int|null, 'message' => string, 'meetsRequirement' => bool]
     */
    public function compress(string $videoPath, ?int $maxSize = null): array
    {
        $targetSize = $maxSize ?? $this->getTargetSize();

        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'originalSize' => filesize($videoPath),
                'compressedSize' => null,
                'message' => 'FFmpeg non disponible sur le serveur',
                'meetsRequirement' => filesize($videoPath) <= $targetSize,
                'finalPath' => $videoPath
            ];
        }

        if (!file_exists($videoPath)) {
            return [
                'success' => false,
                'originalSize' => 0,
                'compressedSize' => null,
                'message' => 'Fichier vidéo introuvable',
                'meetsRequirement' => false,
                'finalPath' => $videoPath
            ];
        }

        $originalSize = filesize($videoPath);
        $isMp4 = strtolower(pathinfo($videoPath, PATHINFO_EXTENSION)) === 'mp4';

        if ($originalSize <= $targetSize && $isMp4) {
            return [
                'success' => true,
                'originalSize' => $originalSize,
                'compressedSize' => $originalSize,
                'message' => 'Vidéo déjà conforme et au format MP4, compression non nécessaire',
                'meetsRequirement' => true,
                'finalPath' => $videoPath
            ];
        }

        try {
            // Try compression with multiple quality levels if needed
            $result = $this->compressWithQualityLevels($videoPath, $originalSize, $targetSize);

            return $result;

        } catch (\Exception $e) {
            Log::error('Exception lors de la compression vidéo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'originalSize' => $originalSize,
                'compressedSize' => null,
                'message' => 'Exception: ' . $e->getMessage(),
                'meetsRequirement' => false,
                'finalPath' => $videoPath
            ];
        }
    }

    /**
     * Try compression with multiple quality levels until target size is met
     *
     * @param string $videoPath
     * @param int $originalSize
     * @param int $targetSize
     * @return array
     */
    private function compressWithQualityLevels(string $videoPath, int $originalSize, int $targetSize): array
    {
        // Quality levels to try (CRF values: lower = better quality, higher = smaller file)
        $qualityLevels = [
            ['crf' => 28, 'resolution' => '720p'],   // First try: Good quality
            ['crf' => 32, 'resolution' => '720p'],   // Second try: Medium quality
            ['crf' => 28, 'resolution' => '540p'],   // Third try: Lower resolution, good quality
            ['crf' => 35, 'resolution' => '480p'],   // Last resort: Low resolution, high compression
        ];

        $tempPath = $videoPath . '.temp.mp4';

        foreach ($qualityLevels as $index => $level) {
            $command = $this->buildCompressionCommand($videoPath, $tempPath, $level['crf'], $level['resolution']);

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                Log::warning('FFmpeg compression attempt failed', [
                    'attempt' => $index + 1,
                    'level' => $level,
                    'return_code' => $returnCode
                ]);
                continue;
            }

            if (!file_exists($tempPath)) {
                continue;
            }

            $compressedSize = filesize($tempPath);

            // Check if we met the target size
            if ($compressedSize <= $targetSize && $compressedSize > 0) {
                $pathInfo = pathinfo($videoPath);
                $finalPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.mp4';

                if (file_exists($videoPath) && $videoPath !== $finalPath) {
                    unlink($videoPath);
                }
                rename($tempPath, $finalPath);

                $reduction = round(($originalSize - $compressedSize) / $originalSize * 100, 2);

                Log::info('Vidéo compressée avec succès', [
                    'original_size' => $originalSize,
                    'compressed_size' => $compressedSize,
                    'reduction_percent' => $reduction,
                    'attempt' => $index + 1,
                    'quality_level' => $level,
                    'final_path' => $finalPath
                ]);

                return [
                    'success' => true,
                    'originalSize' => $originalSize,
                    'compressedSize' => $compressedSize,
                    'message' => "Vidéo compressée: {$originalSize} → {$compressedSize} bytes (-{$reduction}%)",
                    'meetsRequirement' => true,
                    'finalPath' => $finalPath
                ];
            }

            // Try next level
            @unlink($tempPath);
        }

        // All compression attempts failed to meet target size
        return [
            'success' => false,
            'originalSize' => $originalSize,
            'compressedSize' => null,
            'message' => 'Impossible de compresser la vidéo suffisamment. Veuillez utiliser une vidéo plus courte ou de plus faible qualité.',
            'meetsRequirement' => false
        ];
    }

    /**
     * Build FFmpeg compression command
     *
     * @param string $inputPath
     * @param string $outputPath
     * @param int|null $crf Custom CRF value
     * @param string|null $resolution Custom resolution (720p, 540p, 480p)
     * @return string
     */
    private function buildCompressionCommand(string $inputPath, string $outputPath, ?int $crf = null, ?string $resolution = null): string
    {
        // Resolution mapping
        $resolutions = [
            '720p' => ['width' => 1280, 'height' => 720],
            '540p' => ['width' => 960, 'height' => 540],
            '480p' => ['width' => 854, 'height' => 480],
        ];

        $res = $resolutions[$resolution ?? '720p'] ?? $resolutions['720p'];
        $maxWidth = $res['width'];
        $maxHeight = $res['height'];

        $videoCodec = config('video.compression.video_codec', 'libx264');
        $crfValue = $crf ?? config('video.compression.crf', 28);
        $preset = config('video.compression.preset', 'fast');
        $audioCodec = config('video.compression.audio_codec', 'aac');
        $audioBitrate = config('video.compression.audio_bitrate', '128k');

        return sprintf(
            '%s -i %s -vf "scale=\'min(%d,iw)\':\'min(%d,ih)\':force_original_aspect_ratio=decrease" ' .
            '-c:v %s -crf %d -preset %s ' .
            '-c:a %s -b:a %s ' .
            '-movflags +faststart ' .
            '-y %s 2>&1',
            escapeshellarg($this->ffmpegPath),
            escapeshellarg($inputPath),
            $maxWidth,
            $maxHeight,
            $videoCodec,
            $crfValue,
            $preset,
            $audioCodec,
            $audioBitrate,
            escapeshellarg($outputPath)
        );
    }

    /**
     * Find FFmpeg binary path
     *
     * @return string|null
     */
    private function findFFmpeg(): ?string
    {
        // Common FFmpeg locations
        $possiblePaths = [
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg', // macOS with Homebrew
            'ffmpeg', // In PATH
        ];

        foreach ($possiblePaths as $path) {
            $output = [];
            $returnCode = 0;
            exec(sprintf('%s -version 2>&1', escapeshellarg($path)), $output, $returnCode);

            if ($returnCode === 0) {
                Log::info('FFmpeg trouvé', ['path' => $path]);
                return $path;
            }
        }

        Log::warning('FFmpeg non trouvé sur le serveur');
        return null;
    }

    /**
     * Get video information using FFprobe
     *
     * @param string $videoPath
     * @return array|null
     */
    public function getVideoInfo(string $videoPath): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $ffprobePath = str_replace('ffmpeg', 'ffprobe', $this->ffmpegPath);

        $command = sprintf(
            '%s -v quiet -print_format json -show_format -show_streams %s 2>&1',
            escapeshellarg($ffprobePath),
            escapeshellarg($videoPath)
        );

        exec($command, $output, $returnCode);

        if ($returnCode === 0) {
            return json_decode(implode("\n", $output), true);
        }

        return null;
    }
}
