<?php
abstract class Controller
{
    protected function view(string $template, array $data = [], string $layout = 'dashboard'): void
    {
        View::render($template, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function back(): never
    {
        $ref = safe_referer('/dashboard');
        header('Location: ' . $ref);
        exit;
    }

    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function input(string $key, mixed $default = ''): mixed
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }

    /** Validate POST data; on failure flash errors + old input and go back. */
    protected function validate(array $rules): array
    {
        $data = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $_POST);
        $errors = Validator::make($data, $rules);
        if ($errors) {
            Session::flash('errors', $errors);
            Session::flash('old', array_diff_key($data, array_flip(['password', 'password_confirmation', '_csrf'])));
            $this->back();
        }
        return $data;
    }
}
