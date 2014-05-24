<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Sendandchecksms extends Controller
{

	public function action_index()
	{
		header('Content-Type: text/plain');
		$sms = new SMS;
		$sms->send();
		$sms->retrieve_dlrs();
		die('done');
	}

}