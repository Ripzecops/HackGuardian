<?php
require_once __DIR__ . '/../../../config/config.php';
set_flash('Engineer role is no longer active.', 'error');
redirect('index.php');
