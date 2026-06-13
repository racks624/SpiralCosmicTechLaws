#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Modules\RedTeamOps\Scheduler;
$executed = Scheduler::runPendingScans();
echo "Executed {$executed} scheduled scans.\n";
