<?php

gonzo::import('sql');
gonzo::import('filter');

if (gonzo::var('db.debug') == 'on')
{
	gonzo::filter('db.query', function($query)
	{
		echo gonzo::highlight($query);

		return $query;
	});
}

gonzo::filter('gonzo.input', function($input)
{
	return array_map('filter_strip_slashes', $input);
});

class Database
{
	protected $db;
	protected $connected;

	public function __construct()
	{
		$this->connected = false;

		if ( ! $this->connect(gonzo::var('db')))
		{
			gonzo::log('error.db.connect', gonzo::var('db.database').'@'.gonzo::var('db.hostname').':'.gonzo::var('db.port'), $this);
		} else
		{
			$this->connected = true;
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function connect($config)
	{
		return true;
	}

	public function disconnect()
	{
		if ($this->connected)
		{
			$this->connected = false;
		}
	}

	public function is_connected()
	{
		return ($this->connected !== false);
	}

	public function query($query)
	{

	}

	public function error_message()
	{

	}

	public function error_number()
	{

	}

	public function result()
	{

	}
}
