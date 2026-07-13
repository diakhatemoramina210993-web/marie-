<?php
require __DIR__ . '/../includes/citizen-auth.php';
unset($_SESSION['citoyen_id']);
header('Location: /cahier-lumineux-php/index.php');
exit;
