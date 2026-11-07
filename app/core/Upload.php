<?php
class Upload
{
    public const DOCS = [
        'application/pdf' => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    ];
    public const IMAGES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    /**
     * Store an uploaded file under storage/uploads/{dir} with a random name.
     * Returns [path, null] on success or [null, error].
     */
    public static function store(?array $file, string $dir, array $allowed = self::DOCS, ?int $maxBytes = null): array
    {
        $maxBytes ??= config('upload_max_bytes');
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, __('upload.none')];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > $maxBytes) {
            return [null, __('upload.too_big', ['mb' => (int) ($maxBytes / 1048576)])];
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return [null, __('upload.failed')];
        }

        // Real type from file contents, not the name or browser-sent type.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($allowed[$mime]) || ($ext !== $allowed[$mime] && !($ext === 'jpeg' && $allowed[$mime] === 'jpg'))) {
            return [null, __('upload.type', ['types' => strtoupper(implode(', ', array_unique($allowed)))])];
        }

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $target = BASE_PATH . "/storage/uploads/$dir/$name";
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return [null, __('upload.failed')];
        }
        chmod($target, 0640);
        return ["$dir/$name", null];
    }

    public static function delete(?string $relPath): void
    {
        if ($relPath && preg_match('#^[a-z]+/[a-f0-9]{32}\.[a-z]+$#', $relPath)) {
            @unlink(BASE_PATH . '/storage/uploads/' . $relPath);
        }
    }

    /** Stream a stored file to the browser (after the caller checked permission). */
    public static function send(string $relPath, string $downloadName): never
    {
        $path = BASE_PATH . '/storage/uploads/' . $relPath;
        if (!preg_match('#^[a-z]+/[a-f0-9]{32}\.[a-z]+$#', $relPath) || !is_file($path)) {
            abort(404);
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $downloadName) . '.' . $ext;
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . (str_starts_with($mime, 'image/') ? 'inline' : 'attachment') . '; filename="' . $safe . '"');
        if (!headers_sent() && !preg_grep('/^Cache-Control:/i', headers_list())) {
            header('Cache-Control: private, no-store');
        }
        readfile($path);
        exit;
    }
}
