<?php

declare(strict_types=1);

final class FaviconController
{
    public function index(): void
    {
        $path = BASE_PATH . '/public/assets/img/logo.png';
        if (!is_file($path)) {
            http_response_code(404);
            return;
        }
        if (headers_sent()) {
            return;
        }
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=604800');
        readfile($path);
    }
}
