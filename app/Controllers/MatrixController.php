<?php
namespace App\Controllers;

use App\Models\Finding;

class MatrixController extends Controller
{
    public function index()
    {
        $matrix = Finding::getMitreMatrix();
        $this->view('matrix/index', ['matrix' => $matrix]);
    }

    public function data()
    {
        $matrix = Finding::getMitreMatrix();
        $this->json(['matrix' => $matrix]);
    }
}
