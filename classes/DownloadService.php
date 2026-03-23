<?php
// classes/DownloadService.php

namespace App;

class DownloadService {
    private DownloadRepository $downloadRepository;
    private ProjectRepository $projectRepository;
    private const ALLOWED_TYPES = ['pdf', 'image', 'audio', 'video'];

    public function __construct(DownloadRepository $downloadRepository, ProjectRepository $projectRepository) {
        $this->downloadRepository = $downloadRepository;
        $this->projectRepository = $projectRepository;
    }

    public function handleDownloadRequest(int $projectId, string $fileType, ?int $userId, string $ipAddress): array {
        if ($projectId <= 0) {
            return ['success' => false, 'status' => 400, 'error' => 'ID de projet manquant'];
        }

        if (!in_array($fileType, self::ALLOWED_TYPES, true)) {
            return ['success' => false, 'status' => 400, 'error' => 'Type de fichier non supporte'];
        }

        $project = $this->projectRepository->getById($projectId);
        if (!$project) {
            return ['success' => false, 'status' => 404, 'error' => 'Projet introuvable'];
        }

        $source = $this->resolveFileSource($project, $fileType);
        if ($source === false) {
            return ['success' => false, 'status' => 400, 'error' => 'Le type demande ne correspond pas au fichier disponible'];
        }

        $this->downloadRepository->record($projectId, $userId, $ipAddress, $fileType);

        if ($source === null) {
            return [
                'success' => true,
                'status' => 200,
                'downloads' => $this->downloadRepository->getCountByProject($projectId),
            ];
        }

        return [
            'success' => true,
            'status' => 200,
            'file' => $source['file'],
            'filename' => $source['filename'],
        ];
    }

    private function resolveFileSource(array $project, string $fileType): array|false|null {
        $relativePath = null;

        if ($fileType === 'pdf' || $fileType === 'image') {
            $relativePath = $project['thumbnail'] ?? null;
        } elseif (($fileType === 'audio' || $fileType === 'video') && !empty($project['media'])) {
            $relativePath = $project['media'];
        }

        if (!$relativePath || str_starts_with($relativePath, 'http')) {
            return null;
        }

        if (!$this->matchesRequestedType($relativePath, $fileType)) {
            return false;
        }

        $file = __DIR__ . '/../assets/img/' . $relativePath;
        if (!file_exists($file)) {
            return null;
        }

        return [
            'file' => $file,
            'filename' => ($project['title'] ?? 'download') . '.' . pathinfo($relativePath, PATHINFO_EXTENSION),
        ];
    }

    private function matchesRequestedType(string $path, string $fileType): bool {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($fileType) {
            'pdf' => $extension === 'pdf',
            'image' => in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true),
            'audio' => in_array($extension, ['mp3', 'wav', 'ogg', 'oga', 'flac'], true),
            'video' => in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'avi'], true),
            default => false,
        };
    }
}
