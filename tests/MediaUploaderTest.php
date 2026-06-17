<?php

declare(strict_types=1);

use App\MediaUploader;

return function (): void {
    $uploader = new MediaUploader();

    // PNG 1x1 minimal valide (contenu binaire réel, pas une simple extension trompeuse).
    $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    $imagePath = tempnam(sys_get_temp_dir(), 'mediauploader-image-');
    file_put_contents($imagePath, $pngBytes);

    $textPath = tempnam(sys_get_temp_dir(), 'mediauploader-text-');
    file_put_contents($textPath, "ceci n'est pas une image, juste du texte brut.");

    try {
        assertTrue(
            $uploader->validateMime(['tmp_name' => $imagePath], MediaUploader::getAllowedImageMimes()),
            'Un vrai fichier PNG doit etre accepte par validateMime selon les types image autorises'
        );

        assertFalse(
            $uploader->validateMime(['tmp_name' => $textPath], MediaUploader::getAllowedImageMimes()),
            'Un fichier texte brut ne doit pas etre accepte comme image, meme avec une extension trompeuse'
        );

        assertFalse(
            $uploader->validateMime(['tmp_name' => $imagePath], MediaUploader::getAllowedMediaMimes()),
            'Une image ne doit pas etre acceptee comme media audio/video'
        );

        assertFalse(
            $uploader->validateMime(['tmp_name' => ''], MediaUploader::getAllowedImageMimes()),
            'Un fichier absent doit etre rejete'
        );
    } finally {
        unlink($imagePath);
        unlink($textPath);
    }
};
