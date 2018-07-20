<?php
/*
 * AVAILABILITY TEST FILE
 */

// Autoloader START
spl_autoload_register( 'PorterBuddyAutoload' );
function PorterBuddyAutoload($class_name) {

	$file_parts = explode( '\\', $class_name );
	$path = implode(array_slice($file_parts, 1), '/');
	if ($file_parts[0] == 'PorterBuddy' && file_exists($path.'.php')) {
		include_once $path.'.php';
	}
	else return;
}
// Autoloader END

// Error reporting START
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Error reporting END

use PorterBuddy\classes\Buddy;
use PorterBuddy\classes\Address;
use PorterBuddy\classes\Parcel;
use PorterBuddy\classes\Window;
use PorterBuddy\classes\ResolvedAddress;

$buddy = new Buddy('eWGQIc0Eyi3pS1W9bQoc68zB7epL3XO5Ar6QZD5h');

$originAddress = new Address(
	'Keysers Gate',
	'3',
	'0180',
	'Oslo',
	'Norway'
);

$destinationAddress = new Address(
	'Høyenhallveien',
	'25',
	'0678',
	'Oslo',
	'Norway'
);

$window1 = new Window('2019-02-12T10:00+01:00', '2019-02-12T18:00+01:00');
$window2 = new Window('2019-02-13T10:00+01:00', '2019-02-13T18:00+01:00');

$parcel = new Parcel(1, 40, 1, 2000);

$result = $buddy->checkAvailability(
	$originAddress,
	$destinationAddress,
	[$window1, $window2],
	[$parcel],
	['delivery']
);

$originResolvedAddress = ResolvedAddress::load($result->originResolvedAddress);
echo '<pre>';
var_dump($result);
var_dump($originResolvedAddress);