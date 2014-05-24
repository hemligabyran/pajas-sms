<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Admin_Smslog extends Admincontroller
{

	public function before()
	{
		// Set the name of the template to use
		$this->xslt_stylesheet = 'admin/smslog';
		xml::to_XML(array('admin_page' => 'SMSlog'), $this->xml_meta);
	}

	public function action_index()
	{
		if ( ! isset($_GET['page']))
			$_GET['page'] = 1;

		$smsqueue    = Smsqueue::factory();
		$count       = $smsqueue->count();

		$limit       = 100;
		$offset      = ($_GET['page'] - 1) * $limit;

		if ($offset > $count)
			$offset = 0;

		$pages       = ceil($count / $limit);
		$actual_page = floor($offset / $limit) + 1;

		$smsqueue->limit($limit);
		$smsqueue->offset($offset);

		if (isset($_GET['q']))
			$smsqueue->search($_GET['q']);

		$smses = $smsqueue->get();

		xml::to_XML($smses, array('smslog' => $this->xml_content), 'sms', 'id');
		xml::to_XML(array('sms_count' => $count, 'pages' => $pages, 'actual_page' => $actual_page, 'limit' => $limit), array('sms_meta' => $this->xml_content));
	}

}