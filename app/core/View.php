<?php
class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $template, array $data = [], ?string $layout = 'dashboard'): void
    {
        [$content, $vars] = self::capture($template, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        // Pages may set $title, $scripts or $noErrorSummary for the layout.
        $pass = array_intersect_key($vars, array_flip(['title', 'scripts', 'noErrorSummary']));
        echo self::partial('layouts/' . $layout, array_merge($data, $pass, ['content' => $content]));
    }

    public static function partial(string $template, array $data = []): string
    {
        return self::capture($template, $data)[0];
    }

    /** Render a template and also return the variables it defined. */
    private static function capture(string $template, array $data): array
    {
        $file = BASE_PATH . '/app/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException("View not found: $template");
        }
        extract(array_merge(self::$shared, $data), EXTR_SKIP);
        ob_start();
        require $file;
        $__vars = get_defined_vars();
        return [(string) ob_get_clean(), $__vars];
    }
}
