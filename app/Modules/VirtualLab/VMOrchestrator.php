<?php
namespace App\Modules\VirtualLab;

class VMOrchestrator
{
    public function spawn($os)
    {
        // Docker or LXD call
        return ['container_id' => uniqid()];
    }
}
