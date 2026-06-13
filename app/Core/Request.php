<?php
namespace App\Core;

class Request
{
    protected array $input = [];
    protected array $sanitized = [];

    public function __construct()
    {
        $this->input = array_merge($_GET, $_POST);
        $this->parseJsonInput();
        $this->sanitizeInput();
    }

    protected function parseJsonInput()
    {
        if ($this->isJson()) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (is_array($input)) {
                $this->input = array_merge($this->input, $input);
            }
        }
    }

    protected function sanitizeInput()
    {
        foreach ($this->input as $key => $value) {
            if (is_string($value)) {
                $this->sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $this->sanitized[$key] = $value;
            }
        }
    }

    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri()
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return $path ?: '/';
    }

    public function all()
    {
        return $this->sanitized;
    }

    public function input($key, $default = null)
    {
        return $this->sanitized[$key] ?? $default;
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();
        return array_intersect_key($this->sanitized, array_flip($keys));
    }

    public function has($key)
    {
        return isset($this->sanitized[$key]);
    }

    public function isJson()
    {
        return strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false;
    }

    public function csrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrf($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
