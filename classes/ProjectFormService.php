<?php
// classes/ProjectFormService.php

namespace App;

class ProjectFormService {
    private MediaUploader $uploader;
    private ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository, ?MediaUploader $uploader = null) {
        $this->projectRepository = $projectRepository;
        $this->uploader = $uploader ?? new MediaUploader();
    }

    public function getEmptyFormData(array $source = []): array {
        return [
            'title' => $source['title'] ?? '',
            'description' => $source['description'] ?? '',
            'author' => $source['author'] ?? '',
            'arranger' => $source['arranger'] ?? '',
            'genre' => $source['genre'] ?? '',
            'tonality' => $source['tonality'] ?? '',
            'moment_messe' => $source['moment_messe'] ?? null,
            'temps_liturgique' => $source['temps_liturgique'] ?? null,
            'voix' => $source['voix'] ?? null,
            'is_liturgical' => !empty($source['is_liturgical']),
            'thumbnail' => $source['thumbnail'] ?? '',
            'media' => $source['media'] ?? null,
        ];
    }

    public function prepareFromRequest(array $post, array $files, ?array $existingProject = null): array {
        $data = $this->getEmptyFormData([
            'title' => trim($post['title'] ?? ($existingProject['title'] ?? '')),
            'description' => trim($post['description'] ?? ($existingProject['description'] ?? '')),
            'author' => trim($post['author'] ?? ($existingProject['author'] ?? '')),
            'arranger' => trim($post['arranger'] ?? ($existingProject['arranger'] ?? '')),
            'genre' => trim($post['genre'] ?? ($existingProject['genre'] ?? '')),
            'tonality' => trim($post['tonality'] ?? ($existingProject['tonality'] ?? '')),
            'moment_messe' => $post['moment_messe'] ?? ($existingProject['moment_messe'] ?? null),
            'temps_liturgique' => $post['temps_liturgique'] ?? ($existingProject['temps_liturgique'] ?? null),
            'voix' => $post['voix'] ?? ($existingProject['voix'] ?? null),
            'is_liturgical' => isset($post['is_liturgical']),
            'thumbnail' => $existingProject['thumbnail'] ?? '',
            'media' => $existingProject['media'] ?? null,
        ]);

        $errors = [];

        $thumbnailUpload = $this->uploader->uploadValidated(
            $files['file'] ?? [],
            MediaUploader::getAllowedImageMimes(),
            'image',
            $existingProject === null
        );
        $mediaUpload = $this->uploader->uploadValidated(
            $files['media'] ?? [],
            MediaUploader::getAllowedMediaMimes(),
            'media'
        );

        if (!$thumbnailUpload['success'] && !empty($thumbnailUpload['error'])) {
            $errors[] = $thumbnailUpload['error'];
        } elseif (!empty($thumbnailUpload['path'])) {
            $data['thumbnail'] = $thumbnailUpload['path'];
        }

        if (!$mediaUpload['success'] && !empty($mediaUpload['error'])) {
            $errors[] = $mediaUpload['error'];
        } elseif (!empty($mediaUpload['path'])) {
            $data['media'] = $mediaUpload['path'];
        }

        if ($data['title'] === '' || strlen($data['title']) > 100) {
            $errors[] = 'Le titre doit contenir entre 1 et 100 caractères.';
        }

        if ($data['description'] === '') {
            $errors[] = 'La description est obligatoire.';
        }

        return [
            'data' => $data,
            'errors' => $errors,
        ];
    }

    public function create(int $userId, array $data): bool {
        return $this->projectRepository->create(
            $userId,
            $data['title'],
            $data['description'],
            $data['thumbnail'],
            $data['media'],
            $data['author'] ?: null,
            $data['arranger'] ?: null,
            $data['genre'] ?: null,
            $data['tonality'] ?: null,
            $data['moment_messe'] ?: null,
            $data['temps_liturgique'] ?: null,
            (bool) $data['is_liturgical'],
            $data['voix'] ?: null
        );
    }

    public function update(int $projectId, int $userId, array $data): bool {
        return $this->projectRepository->updateByUser(
            $projectId,
            $userId,
            $data['title'],
            $data['description'],
            $data['thumbnail'],
            $data['media'],
            $data['author'] ?: null,
            $data['arranger'] ?: null,
            $data['genre'] ?: null,
            $data['tonality'] ?: null,
            $data['moment_messe'] ?: null,
            $data['temps_liturgique'] ?: null,
            (bool) $data['is_liturgical'],
            $data['voix'] ?: null
        );
    }
}
