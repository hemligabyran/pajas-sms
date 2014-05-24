<?php defined('SYSPATH') OR die('No direct access allowed.');

class Driver_Sms_Jojka extends Driver_Sms
{

	protected function check_db_structure()
	{
		$tables = $this->pdo->query('SHOW TABLES;')->fetchAll(PDO::FETCH_COLUMN);
		if (in_array('sms_queue', $tables))
		{
			$columns = $this->pdo->query('DESCRIBE sms_queue;')->fetchAll(PDO::FETCH_COLUMN);

			if (
				$columns == array(
					'id',
					'remote_id',
					'status',
					'attempts',
					'dlr_status',
					'to',
					'from',
					'msg',
					'queued',
					'sent',
					'dlr_received',
				)) return TRUE;
		}

		return FALSE;
	}

	protected function create_db_structure() {
		return $this->pdo->query('CREATE TABLE `sms_queue` (
				`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				`remote_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
				`status` enum(\'queue\',\'sent\',\'failed\') COLLATE utf8_unicode_ci NOT NULL DEFAULT \'queue\',
				`attempts` int(11) unsigned NOT NULL DEFAULT 0,
				`dlr_status` enum(\'delivered\',\'failed\') COLLATE utf8_unicode_ci DEFAULT NULL,
				`to` bigint(20) unsigned NOT NULL,
				`from` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
				`msg` text COLLATE utf8_unicode_ci NOT NULL,
				`queued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`sent` timestamp NULL DEFAULT NULL,
				`dlr_received` timestamp NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `remote_id` (`remote_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
	}

	public function add($to, $msg, $from = FALSE)
	{
		$to = preg_replace('/[^0-9]/', '', $to);
		if ( ! $from)
			$from = Kohana::$config->load('sms.from');

		if (strlen($to))
		{

			if ($from)
				$sql = 'INSERT INTO sms_queue (`to`, msg, `from`) VALUES('.$to.','.$this->pdo->quote($msg).','.$this->pdo->quote($from).');';
			else
				$sql = 'INSERT INTO sms_queue (`to`, msg) VALUES('.$to.','.$this->pdo->quote($msg).');';

			$this->pdo->exec($sql);

			$sms_id = $this->pdo->lastInsertId();
		}
	}

	public function retrieve_dlrs($amount)
	{
		$this->pdo->exec('LOCK TABLES sms_queue WRITE;');

		$sql = 'SELECT * FROM sms_queue WHERE status = \'sent\' AND dlr_status IS NULL ORDER BY sent LIMIT '.intval($amount).';';
		foreach ($this->pdo->query($sql) as $row)
		{
			$post_array = array(
				'API_key' => Kohana::$config->load('sms.jojka.API_key'),
				'msg_id'  => $row['remote_id'],
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,            Kohana::$config->load('sms.jojka.DLR_URL'));
			curl_setopt($ch, CURLOPT_POST,           TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS,     $post_array);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$response = curl_exec($ch);
			curl_close($ch);

			$response_array = json_decode($response, TRUE);
			if ($response && is_array($response_array) && isset($response_array['status']))
			{
				if ($response_array['status'] == 'DELIVERED')
				{
					Kohana::$log->add(LOG::DEBUG, 'Response from Jojka SMS Gateway for DLR request, sms_id='.$row['id'].' is='.$response);
					$dlr_status = 'delivered';
				}
				else
				{
					Kohana::$log->add(LOG::ERROR, 'Response from Jojka SMS Gateway for DLR request, sms_id='.$row['id'].' is='.strval($response));
					$dlr_status = 'failed';
				}

				$sql = 'UPDATE sms_queue
					SET
						dlr_status = '.$this->pdo->quote($dlr_status).',
						dlr_received = NOW()
					WHERE remote_id = '.$row['remote_id'].';';

				$this->pdo->exec($sql);
			}
			else
			{
				Kohana::$log->add(LOG::ERROR, 'Response from Jojka SMS Gateway for DLR request, sms_id='.$row['id'].' is='.strval($response));
			}
		}

		$this->pdo->exec('UNLOCK TABLES');

		return TRUE;
	}

	public function send($amount)
	{
		$statuses = array();
		$this->pdo->exec('LOCK TABLES sms_queue WRITE;');

		$sql = 'SELECT * FROM sms_queue WHERE status = \'queue\' ORDER BY queued LIMIT '.intval($amount).';';
		foreach ($this->pdo->query($sql) as $row)
		{
			$post_array = array(
				'API_key' => Kohana::$config->load('sms.jojka.API_key'),
				'to'      => $row['to'],
				'msg'     => $row['msg']
			);

			if ($row['from'])
				$post_array['from'] = $row['from'];

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,            Kohana::$config->load('sms.jojka.URL'));
			curl_setopt($ch, CURLOPT_POST,           TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS,     $post_array);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			$response = curl_exec($ch);
			curl_close($ch);

			$response_array = json_decode($response, TRUE);
			if ($response && is_array($response_array) && isset($response_array['message_id']))
			{
				Kohana::$log->add(LOG::DEBUG, 'Response from Jojka SMS Gateway for sms_id='.$row['id'].' is='.$response);

				$sql = 'UPDATE sms_queue
					SET
						attempts  = attempts + 1,
						status    = \'sent\',
						remote_id = '.$this->pdo->quote($response_array['message_id']).',
						sent      = NOW()
					WHERE id = '.$row['id'].';';

				$this->pdo->exec($sql);

				$statuses[$row['id']] = 'Success';
			}
			else
			{
				Kohana::$log->add(LOG::ERROR, 'Response from Jojka SMS Gateway for sms_id='.$row['id'].' is='.strval($response));

				if ($row['attempts'] >= 10)
					$this->pdo->exec('UPDATE sms_queue SET attempts = attempts + 1, status = \'failed\' WHERE id = '.$row['id'].';');
				else
					$this->pdo->exec('UPDATE sms_queue SET attempts = attempts + 1 WHERE id = '.$row['id'].';');

				$statuses[$row['id']] = 'Failed';
			}
		}

		$this->pdo->exec('UNLOCK TABLES');

		return $statuses;
	}

}