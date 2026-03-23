<?php
// classes/MediaUploader.php - Gestion unifiée des uploads (local ou Cloudinary)

namespace App;

class MediaUploader {
    private ?Cloudinary $cloudinary = null;
    private string $localPath = 'assets/img/';
    
    public function __construct() {
        $this->cloudinary = new Cloudinary();
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
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '_' . basename($file['name']);
        $targetPath = $this->localPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return [
                'path' => $filename,
                'public_id' => null,
                'type' => 'local',
                'format' => pathinfo($file['name'], PATHINFO_EXTENSION)
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
        $fullPath = $this->localPath . $path;
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
    
    /**
     * Déterminer le type de ressource Cloudinary
     */
    private function getResourceType(string $filename): string {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi'])) {
            return 'video';
        }
        
        if (in_array($ext, ['mp3', 'wav', 'ogg', 'oga', 'flac'])) {
            return 'video'; // Cloudinary traite l'audio comme vidéo
        }
        
        if ($ext === 'pdf') {
            return 'raw';
        }
        
        return 'image';
    }
    
    /**
     * Déterminer le type de ressource depuis le chemin
     */
    private function getResourceTypeFromPath(string $path): string {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mp3', 'wav', 'oga', 'flac'])) {
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
        
        return in_array($mime, $allowedMimes);
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
