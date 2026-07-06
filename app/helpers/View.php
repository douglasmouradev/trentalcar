<?php

declare(strict_types=1);

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $data['__lang'] = Lang::load();
        extract($data, EXTR_SKIP);
        ob_start();
        $path = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!is_readable($path)) {
            ob_end_clean();
            http_response_code(500);
            echo Lang::get('error.view_not_found');
            return;
        }
        include $path;
        $content = (string) ob_get_clean();
        $layoutPath = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (!is_readable($layoutPath)) {
            http_response_code(500);
            echo Lang::get('error.layout_not_found');
            return;
        }
        include $layoutPath;
    }

    /** @param array<string, mixed> $data */
    public static function partial(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $path = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (is_readable($path)) {
            include $path;
        }
    }
}
