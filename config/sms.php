<?php defined('SYSPATH') or die('No direct script access.');

/*
IMPORTANT! Add this line to your bootstrap when you go live:
Kohana::$environment = Kohana::PRODUCTION;
And have it commented out while developing.
*/

return array(
	'driver'               => 'jojka',
	'from'                 => 'Pajas', // Can be set to FALSE or NULL to use operators default
	'allowed_test_numbers' => array(), // Numbers allowed to send to when not in production

	'jojka' => array(
		'API_key' => '',
		'URL'     => 'https://www.jojka.nu/websms/api/send',
		'DLR_URL' => 'https://www.jojka.nu/websms/api/get_msg_status',
	)
);
