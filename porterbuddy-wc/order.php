<?php
/*
 * ORDER TEST FILE
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
use PorterBuddy\classes\Origin;
use PorterBuddy\classes\Destination;

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

$window = new Window('2019-02-12T10:00+01:00', '2019-02-12T18:00+01:00');

$origin = new Origin(
	'Nils Johansen (sender)',
	$originAddress,
	'testemail+sender@porterbuddy.com',
	'+47',
	'65127865',
	[$window]
);

$destination = new Destination(
	'Roger Olsen (Recipient)',
	$destinationAddress,
	'testemail+recipient@porterbuddy.com',
	'+47',
	'65789832',
	$window,
	[
		'minimumAgeCheck' => 16,
		'leaveAtDoorstep' => false,
		'idCheck' => true,
		'requireSignature' => false,
		'onlyToRecipient' => true
	]
);

$parcel = new Parcel(1, 40, 1, 2000, 'Shoes');

$result = $buddy->placeOrder(
	$origin,
	$destination,
	[$parcel],
	'delivery',
	'Test'
);

echo '<pre>';
echo var_dump($result);