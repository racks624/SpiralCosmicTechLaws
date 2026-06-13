<?php
namespace App\Core;

abstract class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        $viewPath = ROOT_PATH . "/app/Views/{$view}.php";
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        ob_start();
        require $viewPath;
        $content = ob_get_clean();
        // If layout exists, we rely on layout.php inside view, otherwise raw
        if (file_exists(ROOT_PATH . '/app/Views/layout.php') && !isset($data['_no_layout'])) {
            require ROOT_PATH . '/app/Views/layout.php';
        } else {
            echo $content;
        }
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function csrfField()
    {
        $token = (new Request())->csrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    protected function validateCsrf(Request $request)
    {
        if (!$request->validateCsrf($request->input('csrf_token'))) {
            $this->json(['error' => 'CSRF validation failed'], 403);
        }
    }
}
