<?php
namespace App\Controllers;

abstract class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        $viewPath = ROOT_PATH . "/app/Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View {$view} not found");
        }
    }

    protected function json($data, $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
