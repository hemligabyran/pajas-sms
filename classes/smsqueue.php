<?php defined('SYSPATH') OR die('No direct access allowed.');

class Smsqueue
{

	protected $limit;
	protected $pdo;
	protected $search;
	protected $offset;

	public function __construct()
	{
		$this->pdo = Pajas_Pdo::instance();
	}

	public static function factory()
	{
		return new self();
	}

	public function count()
	{
		$sql = 'SELECT COUNT(id) FROM sms_queue WHERE 1';

		if ($this->search !== NULL)
		{
			$sql .= ' AND (
				msg LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `to` LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `from` LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `queued` LIKE '.$this->pdo->quote('%'.$this->search.'%').')';
		}

		return $this->pdo->query($sql)->fetchColumn();
	}

	public function get()
	{
		$sql = 'SELECT * FROM sms_queue WHERE 1';

		if ($this->search !== NULL)
		{
			$sql .= ' AND (
				msg LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `to` LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `from` LIKE '.$this->pdo->quote('%'.$this->search.'%').'
				OR `queued` LIKE '.$this->pdo->quote('%'.$this->search.'%').')';
		}

		$sql .= ' ORDER BY queued DESC';

		if ($this->limit)
		{
			$sql .= ' LIMIT '.intval($this->limit);
			if ($this->offset)
				$sql .= ' OFFSET '.intval($this->offset);
		}

		return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	}

	public function limit($limit)   { $this->limit  = $limit;  return $this;}
	public function search($search) { $this->search = $search; return $this;}
	public function offset($offset) { $this->offset = $offset; return $this;}

}