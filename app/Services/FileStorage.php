<?php
declare(strict_types=1);
namespace App\Services;

class FileStorage
{
    private const MAX_SIZE = 10 * 1024 * 1024; // 10 Mo

    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public static function store(array $file, string $subdir = ''): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Erreur lors du téléversement (code ' . $file['error'] . ').');
        }
        if ($file['size'] > self::MAX_SIZE) {
            throw new \RuntimeException('Fichier trop volumineux (max 10 Mo).');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new \RuntimeException('Type de fichier non autorisé.');
        }

        $config  = require CONFIG_PATH;
        $baseDir = rtrim($config['storage']['documents'], '/');
        $dir     = $subdir ? $baseDir . '/' . trim($subdir, '/') : $baseDir;

        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            throw new \RuntimeException('Impossible de créer le répertoire de stockage.');
        }

        $ext    = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $stored = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
        $path   = $dir . '/' . $stored;

        if (!move_uploaded_file($file['tmp_name'], $path)) {
            throw new \RuntimeException('Impossible de déplacer le fichier.');
        }

        return [
            'original_filename' => basename($file['name']),
            'stored_path'       => $path,
            'mime_type'         => $mime,
            'file_size'         => (int)$file['size'],
        ];
    }

    public static function serve(string $storedPath, string $originalFilename, string $mime): never
    {
        $real    = realpath($storedPath);
        $baseDir = realpath(ROOT_PATH . '/storage');

        if ($real === false || $baseDir === false || strncmp($real, $baseDir, strlen($baseDir)) !== 0) {
            http_response_code(404);
            exit('Fichier introuvable.');
        }

        header('Content-Type: '        . $mime);
        header('Content-Disposition: attachment; filename="' . rawurlencode($originalFilename) . '"');
        header('Content-Length: '      . filesize($real));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($real);
        exit;
    }
}
