<?php
// Return availability information as json
$result = include 'includes/availability.php';

header('Content-Type: application/json');
if(1==0)
{
	// Test Code
	$result['data']['express'] = array([
		'product' => 'express',
		'start' => "2018-09-17T18:00:00+02:00",
		'end' => "2018-09-17T19:30:00+02:00",
		'price' => [
			'fractionalDenomination' => 24900,
			'currency' => 'NOK',
			'string' => '249,00',
			'return' => '328,00'
		]
	]);
}

echo json_encode($result);