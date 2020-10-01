<?php

gonzo::import('session');

class Session_Native extends Session
{
	protected $session_start;

	public function __construct()
	{
		$this->session_start = false;

		$this->create();
	}

	public function create($session_time = 3600 * 24)
	{
		if ( ! $this->session_start)
		{
			ini_set('session.gc_maxlifetime', $session_time);

			session_set_cookie_params($session_time);
			session_start();

			$_SESSION['session_update'] = time();

			if ( ! isset($_SESSION['session_start']))
			{
				$_SESSION['session_start'] = time();
			} else if (time() - $_SESSION['session_start'] > $session_time / 2)
			{
				session_regenerate_id(true);

				$_SESSION['session_start'] = time();
			}

			$this->session_start = true;
		}
	}

	public function destroy()
	{
		$_SESSION = array();

		session_unset();
		session_destroy();

		$this->session_start = false;
	}

	public function get($key)
	{
		if ( ! $this->session_start)
		{
			return false;
		}

		return ((isset($_SESSION[$key]) && ! empty($_SESSION[$key])) ? $_SESSION[$key] : false);
	}

	public function set($key, $value = null)
	{
		if (is_array($key))
		{
			foreach($key as $k => $b)
			{
				$_SESSION[$k] = $v;
			}
		} else
		{
			$_SESSION[$key] = $value;
		}
	}

	public function clear($key)
	{
		unset($_SESSION[$key]);
	}

	public function exists($key)
	{
		return (isset($_SESSION[$key]) AND ! empty($_SESSION[$key]));
	}
}

gonzo::instanceof('Session', 'Session_Native');
gonzo::instance('session');
