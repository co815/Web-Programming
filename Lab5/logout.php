<?php
require_once 'includes/auth.php';
session_regenerate_id(true);
session_destroy();
header('Location: index.php');
exit;
