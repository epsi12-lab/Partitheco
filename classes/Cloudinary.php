<?php
// classes/Cloudinary.php - Gestion du stockage Cloudinary

declare(strict_types=1);

namespace App;

class Cloudinary {
    private string $cloudName;
    private string $apiKey;
    private string $apiSecret;
    private string $uploadPreset;
    
    public function __construct() {
        $this->cloudName = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '';
        $this->apiKey = $_ENV['CLOUDINARY_API_KEY'] ?? '';
        $this->apiSecret = $_ENV['CLOUDINARY_API_SECRET'] ?? '';
        $this->uploadPreset = $_ENV['CLOUDINARY_UPLOAD_PRESET'] ?? 'partitheco';
    }
    
    public function isConfigured(): bool {
        return !empty($this->cloudName) && !empty($this->apiKey) && !empty($this->apiSecret);
    }
    
    /**
     * Upload un fichier vers Cloudinary
     */
    public function upload(string $filePath, array $options = []): ?array {
        if (!$this->isConfigured()) {
            return null;
        }
        
        $timestamp = time();
        $folder = $options['folder'] ?? 'partitheco';
        $resourceType = $options['resource_type'] ?? 'auto';
        
        // Paramètres à signer
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        
        // Générer la signature
        $signature = $this->generateSignature($params);
        
        // Préparer la requête
        $postFields = [
            'file' => new \CURLFile($filePath),
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
        ];
        
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/upload";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        error_log("Cloudinary upload error: " . $response);
        return null;
    }
    
    /**
     * Upload depuis une URL
     */
    public function uploadFromUrl(string $url, array $options = []): ?array {
        if (!$this->isConfigured()) {
            return null;
        }
        
        $timestamp = time();
        $folder = $options['folder'] ?? 'partitheco';
        
        $params = [
            'folder' => $folder,
            'timestamp' => $timestamp,
        ];
        
        $signature = $this->generateSignature($params);
        
        $postFields = [
            'file' => $url,
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
            'folder' => $folder,
        ];
        
        $apiUrl = "https://api.cloudinary.com/v1_1/{$this->cloudName}/auto/upload";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 ? json_decode($response, true) : null;
    }
    
    /**
     * Supprimer un fichier
     */
    public function delete(string $publicId, string $resourceType = 'image'): bool {
        if (!$this->isConfigured()) {
            return false;
        }
        
        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        
        $signature = $this->generateSignature($params);
        
        $postFields = [
            'public_id' => $publicId,
            'api_key' => $this->apiKey,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ];
        
        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$resourceType}/destroy";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
    
    /**
     * Générer une URL optimisée
     */
    public function getUrl(string $publicId, array $transformations = []): string {
        $baseUrl = "https://res.cloudinary.com/{$this->cloudName}/image/upload";
        
        $transforms = [];
        if (!empty($transformations['width'])) $transforms[] = 'w_' . $transformations['width'];
        if (!empty($transformations['height'])) $transforms[] = 'h_' . $transformations['height'];
        if (!empty($transformations['crop'])) $transforms[] = 'c_' . $transformations['crop'];
        if (!empty($transformations['quality'])) $transforms[] = 'q_' . $transformations['quality'];
        if (!empty($transformations['format'])) $transforms[] = 'f_' . $transformations['format'];
        
        $transformString = !empty($transforms) ? implode(',', $transforms) . '/' : '';
        
        return "{$baseUrl}/{$transformString}{$publicId}";
    }
    
    /**
     * Générer la signature pour l'API
     */
    private function generateSignature(array $params): string {
        ksort($params);
        $toSign = http_build_query($params) . $this->apiSecret;
        return sha1($toSign);
    }
}
