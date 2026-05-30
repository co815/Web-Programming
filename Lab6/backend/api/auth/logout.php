<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../cors.php';

session_destroy();
json_out(['ok' => true]);
