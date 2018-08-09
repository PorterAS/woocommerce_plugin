<?php
// Return availability information as json
$result = include 'includes/availability.php';

header('Content-Type: application/json');
echo json_encode($result);