<?php

gonzo::import('database');

class Database_MySqli extends Database
{
	protected $result = array();
	protected $cache = array();
	protected $cache_id = null;
	protected $queries = array();

	public function connect($config)
	{
		if ($this->pconnect($config) === false)
		{
			return false;
		}

		return $this;
	}

	public function pconnect($config)
	{
		$this->prefix = $config['prefix'];
		$this->connection = @mysqli_connect($config['hostname'], $config['username'], $config['password'], $config['database']);

		if ($this->connection == null)
		{
			return false;
		}

		return $this;
	}

	public function disconnect()
	{

	}

	public function set_charset($charset, $collation)
	{
		if ($this->connection)
		{
			return $this->connection->set_charset(gonzo::var('gonzo.charset'));
		}
	}

	protected function escape_string($str)
	{
		if (function_exists('mysqli_real_escape_string'))
		{
			return '\''.mysqli_real_escape_string($str).'\'';
		} elseif (function_exists('mysqli_escape_string'))
		{
			return '\''.mysqli_escape_string($str).'\'';
		} else
		{
			return '\''.addslashes($str).'\'';
		}
	}

	public function query($query)
	{
		$this->id = false;
		$this->result = array();

		$hash = filter_hash($query);

		if (gonzo::var('db.cache') == 'on' && $nocache == false && stripos($query, 'select') && stripos($query, 'from'))
		{
			$this->cache_id = $hash;
		} else
		{
			$this->cache_id = null;
		}

		if (gonzo::var('db.cache') == 'off' || $this->cache_id == null || ! isset($this->cache[$this->cache_id]))
		{
			$args = func_get_args();
			$query = gonzo::filtered('db.query', call_user_func_array('sql_filter', $args), $args, $this);

			$time = microtime(true);
			$this->id = @mysqli_query($this->connection, $query);

			$this->queries[] = array
			(
				'query' => $query,
				'time' => substr(((microtime(true) - $time) / 1000.0).'', 0, 4).'ms'
			);

			if ($this->id == false)
			{
				if ($this->error_number() != 1062)
				{
					gonzo::log('error.db.query', '', $this);
				}

				return false;
			}
		}

		return $this->id;
	}

	public function create_table($table, $fields, $types = array(), $unique = array())
	{
		$check_table = 'select 1 from '.$table;
		$create_table = 'create table if not exists `'.$table.'` (`id` bigint unsigned not null auto_increment, ';

		$tables = gonzo::var('db.tables');

		if ( ! is_array($tables))
		{
			$tables = array();
		}

		if (in($tables, $table))
		{
			return true;
		}

		foreach ($fields as $field => $default)
		{
			$type = 'varchar(255)';

			if (isset($types[$field]))
			{
				$type = $types[$field];
			} else if (is_numeric($default))
			{
				$type = 'int(11)';
			}

			$create_table .= '`'.$field.'` '.$type.' not null default '.(is_string($default) || is_numeric($default) ? '\''.$default.'\'' : '').', ';
		}

		$create_table .= 'primary key (`id`)'.( ! empty($unique) ? ', unique key `'.$unique[0].'` (`'.implode('`, `', $unique).'`)' : '').') engine=MyISAM default charset=utf8;';

		$this->query($create_table);

		if ($this->query($check_table))
		{
			$tables[] = $table;
			$tables = array_unique($tables);

			gonzo::var('db.tables', $tables, true);
		}
	}

	public function affected_rows()
	{
		if ($this->connection)
		{
			return $this->connection->affected_rows;
		}

		return null;
	}

	public function insert_id()
	{
		if ($this->connection)
		{
			return $this->connection->insert_id;
		}
	}

	public function seek($offset)
	{
		return $this->id->data_seek($offset);
	}

	public function fetch_row()
	{
		return $this->id->fetch_row();
	}

	public function fetch_assoc()
	{
		return $this->id->fetch_assoc();
	}

	public function num_rows()
	{
		if ($this->connection && $this->id)
		{
			return $this->id->num_rows;
		}

		return 0;
	}

	public function error_message()
	{
		if ($this->connection)
		{
			return $this->connection->error;
		}

		return '';
	}

	public function error_number()
	{
		if ($this->connection)
		{
			return $this->connection->errno;
		}

		return -1;
	}

	public function result()
	{
		if (gonzo::var('db.cache') == 'on' && $this->cache_id != null && isset($this->cache[$this->cache_id]))
		{
			return $this->cache[$this->cache_id]['result'];
		}

		if (count($this->result) == 0)
		{
			if ($this->id === false || $this->num_rows() == 0)
			{
				return array();
			}

			$this->seek(0);

			while ($row = $this->fetch_assoc())
			{
				$this->result[] = $row;
			}
		}

		if (gonzo::var('db.cache') == 'on' && $this->cache_id != null)
		{
			$this->cache[$this->cache_id] = array
			(
				'id' => $this->id,
				'result' => $this->result
			);
		}

		return $this->result;
	}

	public function get_queries()
	{
		return $this->queries;
	}
}

gonzo::instanceof('Database', 'Database_MySqli');
gonzo::instance('database');
