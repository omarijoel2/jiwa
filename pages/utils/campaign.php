<?php
require_once __DIR__ . '/../../Config/autoload.php';

use App\Classes\Logger;

$logger = new Logger();

$logger->info('Campaign page loaded');