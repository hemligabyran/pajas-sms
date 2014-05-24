<?php defined('SYSPATH') OR die('No direct access allowed.');

abstract class Driver_Sms extends Model
{

	public function __construct()
	{
		parent::__construct();
		if (Kohana::$environment == Kohana::DEVELOPMENT)
			if ( ! $this->check_db_structure())
				$this->create_db_structure();
	}

	/**
	 * Returns true/false depending on if the db structure exists or not
	 *
	 * @author Johnny Karhinen, http://fullkorn.nu, johnny@fullkorn.nu
	 * @return boolean
	 */
	abstract protected function check_db_structure();

	/**
	 * Create the db structure
	 *
	 * @return boolean
	 */
	abstract protected function create_db_structure();

	/**
	 * Add sms to queue
	 *
	 * @param str $to
	 * @param str $msg
	 * @param str $from
	 *
	 * @return int ID in database
	 */
	abstract public function add($to, $msg, $from = FALSE);

	/**
	 * Get DLRs for sent messages
	 *
	 * @param int $amount - amount of smses to demand DLRs for. Dont set this to high. Maybe 5-10 is good
	 * @return array with sucesses
	 */
	abstract public function retrieve_dlrs($amount);

	/**
	 * Send smses from the queue
	 *
	 * @param int $amount - amount of smses to send. Dont set this to high. Maybe 5-10 is good
	 * @return array with sucesses
	 */
	abstract public function send($amount);

}