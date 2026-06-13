<?php
namespace App\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $this->view('dashboard', ['title' => 'Spiral Cosmic Tech Laws']);
    }
}
