<?php
require_once __DIR__ . '/../config/config.php';

if (!empty($_SESSION['user_role']) && is_valid_role($_SESSION['user_role'])) {
    redirect('app/views/dashboard/' . $_SESSION['user_role'] . '.php');
}

redirect('index.php');
