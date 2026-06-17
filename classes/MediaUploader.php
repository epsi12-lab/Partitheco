<?php
// classes/MediaUploader.php - Gestion unifiée des uploads (local ou Cloudinary)

declare(strict_types=1);

namespace App;

class MediaUploader {
    private ?Cloudinary $cloudinary = null;
    private string $localPath = 'assets/img/';

    private const EXTENSION_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'application/pdf' => 'pdf',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/x-wav' => 'wav',
        'audio/ogg' => 'ogg',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
    ];

    public function __construct() {
        $this->cloudinary = new Cloudinary();
    }

    /**
     * Dossier de destination absolu pour les uploads locaux (indépendant du cwd d'exécution).
     */
    private function localDir(): string {
        return dirname(__DIR__) . '/public/' . $this->localPath;
    }
    
    /**
     * Vérifie si Cloudinary est configuré et actif
     */
    public function useCloudinary(): bool {
        return $this->cloudinary->isConfigured();
    }
    
    /**
     * Upload un fichier (image, PDF, audio, vidéo)
     * Retourne le chemin/URL du fichier ou null en cas d'erreur
     */
    public function upload(array $file, string $type = 'image'): ?array {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Déterminer le type de ressource pour Cloudinary
        $resourceType = $this->getResourceType($file['name']);
        
        // Si Cloudinary est configuré, l'utiliser
        if ($this->useCloudinary()) {
            $result = $this->cloudinary->upload($file['tmp_name'], [
                'folder' => 'partitheco/' . $type,
                'resource_type' => $resourceType
            ]);
            
            if ($result) {
                return [
                    'path' => $result['secure_url'],
                    'public_id' => $result['public_id'],
                    'type' => 'cloudinary',
                    'format' => $result['format'] ?? pathinfo($file['name'], PATHINFO_EXTENSION)
                ];
            }
        }
        
        // Fallback: upload local
        return $this->uploadLocal($file);
    }
    
    /**
     * Upload local (fallback)
     */
    private function uploadLocal(array $file): ?array {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $ext = self::EXTENSION_BY_MIME[$mime] ?? 'bin';

        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $targetPath = $this->localDir() . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'path' => $filename,
                'public_id' => null,
                'type' => 'local',
                'format' => $ext,
            ];
        }

        return null;
    }
    
    /**
     * Supprimer un fichier
     */
    public function delete(string $path, ?string $publicId = null, string $type = 'local'): bool {
        if ($type === 'cloudinary' && $publicId && $this->useCloudinary()) {
            $resourceType = $this->getResourceTypeFromPath($path);
            return $this->cloudinary->delete($publicId, $resourceType);
        }
        
        // Suppression locale
        $fullPath = $this->localDir() . basename($path);
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    /**
     * Obtenir l'URL complète d'un fichier
     */
    public function getUrl(string $path): string {
        // Si c'est déjà une URL complète (Cloudinary)
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        // Sinon, c'est un fichier local
        return $this->localPath . $path;
    }
    
    private const VIDEO_AND_AUDIO_EXTENSIONS = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mp3', 'wav', 'oga', 'flac'];

    /**
     * Déterminer le type de ressource Cloudinary à partir d'un nom de fichier
     */
    private function getResourceType(string $filename): string {
        return $this->resourceTypeForExtension(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Déterminer le type de ressource Cloudinary à partir d'un chemin
     */
    private function getResourceTypeFromPath(string $path): string {
        return $this->resourceTypeForExtension(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Cloudinary traite l'audio comme de la vidéo, d'où le regroupement video+audio.
     */
    private function resourceTypeForExtension(string $extension): string {
        $ext = strtolower($extension);

        if (in_array($ext, self::VIDEO_AND_AUDIO_EXTENSIONS, true)) {
            return 'video';
        }
        
        if ($ext === 'pdf') {
            return 'raw';
        }
        
        return 'image';
    }
    
    /**
     * Valider le type MIME d'un fichier
     */
    public function validateMime(array $file, array $allowedMimes): bool {
        if (empty($file['tmp_name'])) {
            return false;
        }
        
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        return in_array($mime, $allowedMimes, true);
    }

    /**
     * Upload avec validation MIME et message d'erreur standardise.
     */
    public function uploadValidated(array $file, array $allowedMimes, string $type, bool $required = false): array {
        if (empty($file['tmp_name'])) {
            return [
                'success' => !$required,
                'path' => null,
                'error' => $required ? 'Le fichier principal est requis.' : null,
            ];
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Erreur lors de l'envoi du fichier.",
            ];
        }

        if (!$this->validateMime($file, $allowedMimes)) {
            return [
                'success' => false,
                'path' => null,
                'error' => $type === 'media'
                    ? 'Type de media non autorise (MP3, WAV, OGG, MP4, WEBM acceptes).'
                    : 'Type de fichier non autorise (JPG, PNG, GIF et PDF acceptes).',
            ];
        }

        $uploaded = $this->upload($file, $type);
        if ($uploaded === null || empty($uploaded['path'])) {
            return [
                'success' => false,
                'path' => null,
                'error' => "Echec de l'upload du fichier.",
            ];
        }

        return [
            'success' => true,
            'path' => $uploaded['path'],
            'error' => null,
        ];
    }
    
    /**
     * Types MIME autorisés pour les images/PDF
     */
    public static function getAllowedImageMimes(): array {
        return ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    }
    
    /**
     * Types MIME autorisés pour les médias audio/vidéo
     */
    public static function getAllowedMediaMimes(): array {
        return [
            'audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/x-wav',
            'video/mp4', 'video/webm', 'video/ogg'
        ];
    }
}
