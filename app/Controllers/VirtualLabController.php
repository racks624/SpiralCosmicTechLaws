<?php
namespace App\Controllers;

class VirtualLabController extends Controller
{
    public function index() { $this->view('virtuallab/index'); }
    public function spawnMachine() { $this->json(['machine_id' => uniqid('vm_')]); }
}
