<?php
require_once __DIR__ . '/../../../Config/autoload.php';
use App\Classes\Users;

Users::createUser('jiwa', 'admin@admin.com', 'password', 1);