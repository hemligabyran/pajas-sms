<?php defined('SYSPATH') OR die('No direct access allowed.');

class Sms
{

	private static $driver;

	/**
	 * Set the driver
	 *
	 * @return boolean
	 */
	public static function set_driver()
	{
		$driver_name = 'Driver_Sms_'.ucfirst(Kohana::$config->load('sms.driver'));
		return (self::$driver = new $driver_name);
	}

	/**
	 * Loads the driver if it has not been loaded yet, then returns it
	 *
	 * @return Driver object
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 */
	public static function driver()
	{
		if (self::$driver == NULL) self::set_driver();
		return self::$driver;
	}

	public static function factory()
	{
		return new self();
	}

	public function add($to, $msg, $from = FALSE)
	{
		return self::driver()->add($to, $msg, $from);
	}

	public function retrieve_dlrs($amount = 5)
	{
		return self::driver()->retrieve_dlrs($amount);
	}

	public function send($amount = 5)
	{
		return self::driver()->send($amount);
	}

}