<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CloudinaryService
{
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private string $uploadUrl;

    public function __construct()
    {
        $this->cloudName = config('services.cloudinary.cloud_name');
        $this->apiKey    = config('services.cloudinary.api_key');
        $this->apiSecret = config('services.cloudinary.api_secret');
        $this->uploadUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
    }

    /**
     * Téléverse une image vers Cloudinary et retourne l'URL sécurisée.
     *
     * @throws \RuntimeException
     */
    public function upload(UploadedFile $file, string $folder = 'tontitogo/avatars'): string
    {
        $timestamp    = time();
        $paramsToSign = "folder={$folder}&timestamp={$timestamp}{$this->apiSecret}";
        $signature    = sha1($paramsToSign);

        $response = Http::withOptions($this->httpOptions())
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($this->uploadUrl, [
                'api_key'   => $this->apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
                'folder'    => $folder,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Échec du téléversement sur Cloudinary : ' . $response->body());
        }

        return $response->json('secure_url');
    }

    public function delete(string $publicId): bool
    {
        $timestamp    = time();
        $paramsToSign = "public_id={$publicId}&timestamp={$timestamp}{$this->apiSecret}";
        $signature    = sha1($paramsToSign);

        $response = Http::withOptions($this->httpOptions())
            ->post(
                "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy",
                [
                    'api_key'   => $this->apiKey,
                    'timestamp' => $timestamp,
                    'signature' => $signature,
                    'public_id' => $publicId,
                ]
            );

        return $response->successful() && $response->json('result') === 'ok';
    }

    
    public function extractPublicId(string $url): string
    {
        preg_match('/upload\/(?:v\d+\/)?(.+)\.[a-z]+$/i', $url, $matches);

        return $matches[1] ?? '';
    }

    private function httpOptions(): array
    {
        // Chemin vers un bundle CA custom (optionnel)
        $cacertPath = storage_path('app/cacert.pem');

        if (app()->isLocal() && ! config('services.cloudinary.verify_ssl', true)) {
            // ⚠️ DÉVELOPPEMENT UNIQUEMENT — ne jamais mettre en prod
            return ['verify' => false];
        }

        if (file_exists($cacertPath)) {
            return ['verify' => $cacertPath];
        }

        return [];
    }
}
