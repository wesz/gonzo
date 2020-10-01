<?php

define('GONZO', microtime(true));

defined('GONZO_EXT') || define('GONZO_EXT', '.php');
defined('GONZO_SLASH') || define('GONZO_SLASH', '/');
defined('GONZO_NL') || define('GONZO_NL', "\n");

defined('GONZO_PATH') || define('GONZO_PATH', realpath(dirname(__FILE__)).GONZO_SLASH);

function path_base($path)
{
	if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches))
	{
		return $matches[1];
	} else if (preg_match('@^([^\\\\/]+)$@s', $path, $matches))
	{
		return $matches[1];
	}

	return '';
}

function path_ext($path)
{
	$ext = explode('.', path_base($path));

	if (count($ext) > 1)
	{
		return '.'.end($ext);
	}

	return '';
}

function path_dir($path, $dir_name = false)
{
	$path = rtrim(ltrim($path, '/'), '.');
	$ext = path_ext($path);

	if ( ! empty($ext))
	{
		$path = substr($path, 0, -strlen($ext));
	}

	if ($dir_name)
	{
		$path = rtrim(dirname($path), '/\\');
	}

	if (empty($path) || $path == '/')
	{
		return $path;
	}

	$path = rtrim($path, ' ./\\');

	if ( ! empty($path))
	{
		$path .= '/';
	}

	return $path;
}

function path_trim($path)
{
	// this function does not follow symlinks
	// replace it with return realpath($path) to respect symlinks
	$hash = explode('#', $path);
	$query = explode('?', $hash[0]);
	$path = explode('/', $query[0]);

	if (count($path) <= 1)
	{
		return implode('/', $path);
	}

	$keys = array_keys($path, '..');

	foreach ($keys as $keypos => $key)
	{
		array_splice($path, $key - ($keypos * 2 + 1), 2);
	}

	$path = implode('/', $path);


	return $path;
}

function path_scan($path, $desc = true)
{
	$ignored = array('.', '..', '.svn', '.htaccess');
	$files = array();
	$dir = scandir($path);

	foreach ($dir as $file)
	{
		if (in_array($file, $ignored))
		{
			continue;
		}

		$files[$file] = filemtime($path.'/'.$file);
	}

	if ($desc)
	{
		asort($files);
	} else
	{
		arsort($files);
	}

	$files = array_keys($files);

	return ($files) ? $files : false;
}

function file_ensure($path, $default = '', $update = false)
{
	$dir = path_dir($path);

	dir_ensure($dir);

	return (is_file($path) && ! $update) || file_put_contents($path, $default);
}

function dir_ensure($path, $permissions = 0777)
{
	$path = trim($path);

	if (empty($path) || $path == '.' || $path == '/' || is_dir($path))
	{
		return true;
	}

	return mkdir($path, 0755, true);
}

function is_assoc($array)
{
	if ( ! is_array($array))
	{
		return false;
	}

	return (bool)count(array_filter(array_keys($array), 'is_string'));
}

function assoc($array)
{
	if (is_assoc($array))
	{
		return $array;
	}

	$assoc = array();

	foreach ($array as $index => $value)
	{
		$assoc[$index.''] = $value;
	}

	return $assoc;
}

function in(array $array, $value)
{
	if (is_assoc($array))
	{
		return in_array($value, array_keys($array));
	}

	return in_array($value, $array);
}

function is($array, $key, $value = null)
{
	return isset($array[$key]) && ($value === null ? ! empty($array[$key]) : in_array($array[$key], ( ! is_array($value) ? array($value) : $value)));
}

function not($array, $key, $value = null)
{
	return ! isset($array[$key]) || ($value === null ? empty($array[$key]) : ! in_array($array[$key], ( ! is_array($value) ? array($value) : $value)));
}

function extend($base, $array)
{
	foreach ($base as $key => $value)
	{
		if ( ! isset($array[$key]))
		{
			$array[$key] = $value;
		}
	}

	return $array;
}

function exclude($array, $excludee)
{
	if (is_assoc($array))
	{
		return $array;
	}

	if ( ! is_array($excludee))
	{
		$excludee = array($excludee);
	}

	if (is_assoc($array))
	{
		return array_diff_assoc($array, $excludee);
	}

	return array_diff($array, $excludee);
}

function time_parse($time)
{
	$timestamp = 'YMdhms';
	$counter = array
	(
		'Y' => 1 * 60 * 60 * 24 * 31 * 12,
		'M' => 1 * 60 * 60 * 24 * 31,
		'd' => 1 * 60 * 60 * 24,
		'h' => 1 * 60 * 60,
		'm' => 1 * 60,
		's' => 1
	);

	$len = strlen($timestamp);

	$result = 0;

	for ($i = 0; $i < $len; $i++)
	{
		preg_match('|(?<number>\d+)'.$timestamp[$i].'|', $time, $match);

		if (isset($match['number']))
		{
			$tmp = $match['number'];

			if ($tmp)
			{
				$result += $tmp * $counter[$timestamp[$i]];
			}
		}
	}

	return $result;
}

class gonzo
{
	protected static $initialized;
	protected static $var;
	protected static $memcache;
	protected static $routes;
	protected static $instances;
	protected static $locale;
	protected static $plugins;
	protected static $filters;
	protected static $log;

	public static $http_status = array
	(
		100 => '100 Continue',
		101 => '101 Switching Protocols',
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported'
	);

	public static $http_mime = array
	(
		'text' => 'text/plain',
		'txt' => 'text/plain',
		'plain' => 'text/plain',
		'html' => 'text/html',
		'xml' => 'text/xml',
		'javascript' => 'application/javascript',
		'js' => 'application/javascript',
		'json' => 'application/json',
		'css' => 'text/css',
		'csv' => 'text/csv',
		'png' => 'image/png',
		'jpg' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'gif' => 'image/gif'
	);

	public static function filtered()
	{
		$args = func_get_args();

		if (is_array($args[0]))
		{
			$args = $args[0];
		}

		$name = array_shift($args);
		$output = null;

		if (isset($args[0]))
		{
			$output = $args[0];
		}

		if (not(self::$filters, $name))
		{
			return $output;
		} else
		{
			foreach (self::$filters[$name] as $priority => $functions)
			{
				foreach ($functions as $function)
				{
					$output = call_user_func_array($function, $args);

					if ($output !== null && ! is_object($output))
					{
						$args[0] = $output;
					}
				}
			}
		}

		return $output;
	}

	public static function filter($name, $function, $priority = 1)
	{
		if (not(self::$filters, $name))
		{
			self::$filters[$name] = array();
		}

		if ($priority < 1)
		{
			$priority = 1;
		} else if ($priority > 10)
		{
			$priority = 10;
		}

		if (not(self::$filters[$name], $priority))
		{
			self::$filters[$name][$priority] = array();
		}

		self::$filters[$name][$priority][] = $function;
	}

	public static function filter_remove($name, $function)
	{
		if (is(self::$filters, $name))
		{
			foreach (self::$filters[$name] as $priority => $functions)
			{
				foreach ($functions as $key => $callback)
				{
					if ($function == $callback)
					{
						unset(self::$filters[$name][$priority][$key]);
					}
				}
			}
		}
	}

	public static function filter_remove_all($name)
	{
		if (is(self::$filters, $name))
		{
			unset(self::$filters[$name]);
		}
	}

	public static function trigger()
	{
		$args = func_get_args();
		$args[0] = 'on_'.$args[0];

		return self::filtered($args);
	}

	public static function on($name, $function, $args = array(), $priority = 1)
	{
		if (substr($name, 0, 1) == '/' || substr($name, 0, 2) == '!/')
		{
			self::$routes[$name] = $args;
		}

		self::filter('on_'.$name, $function, $priority);
	}

	public static function off($name, $function)
	{
		if (substr($name, 0, 1) == '/' || substr($name, 0, 2) == '!/')
		{
			usnet(self::$routes[$name]);
		}

		self::filter_remove('on_'.$name, $function);
	}

	public static function setup()
	{
		self::$initialized = false;
		self::$instances = array();
		self::$locale = array();
		self::$routes = array();
		self::$plugins = array();
		self::$memcache = array('var' => array(), 'lang' => array());

		self::$var = array
		(
			'gonzo' => array
			(
				'name' => 'Gonzo',
				'debug' => 'off',
				'footnote' => 'on',
				'lang' => 'en',
				'charset' => 'utf-8',
				'timezone' => date_default_timezone_get(),
				'salt' => '',
				'file' => basename($_SERVER['SCRIPT_NAME']),
				'url' => $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']),
				'path' => '',
				'secure' => ($_SERVER['REQUEST_SCHEME'] == 'https' ? 'on' : 'off'),
				'content' => '',
				'content_type' => 'text/html',
				'status' => 200,
				'view_dir' => '',
				'view_icon' => '',
				'view_title' => '',
				'view_keywords' => '',
				'view_description' => '',
				'auto_route' => 'on',
				'i18n_dir' => 'i18n',
				'i18n_domain' => 'gonzo',
				'i18n_lang' => 'en',
				'plugin_dir' => 'plugins',
				'media_dir' => 'media',
				'upload_dir' => 'uploads',
				'cache' => 'on',
				'cache_dir' => 'cache'
			),

			'db' => array
			(
				'driver' => '',
				'hostname' => 'localhost',
				'port' => '3306',
				'database' => 'database',
				'username' => 'username',
				'password' => '',
				'prefix' => 'gonzo_',
				'debug' => 'off',
				'cache' => 'off'
			),

			'session' => array
			(
				'driver' => 'Native'
			)
		);

		self::trigger('gonzo.setup');
	}

	public static function init()
	{
		self::$log = '';

		if ( ! file_exists(__DIR__.'/.gonzo'))
		{
			$f = @fopen(__DIR__.'/.gonzo', 'a');

			if ($f === false)
			{
				die('Insufficient permissions for "'.__DIR__.'" path. Gonzo is unable to write required files.');
			} else
			{
				fclose($f);

				$dirs = array('view', 'i18n', 'plugin', 'media', 'upload', 'cache');

				foreach ($dirs as $dir)
				{
					$dir = path_dir(self::var('gonzo.'.$dir.'_dir'));

					if ( ! empty($dir))
					{
						//dir_ensure(GONZO_PATH.$dir);
					}
				}

				file_ensure('.htaccess', "Options +FollowSymLinks\nOptions All -Indexes\n\nRewriteEngine On\nRewriteBase /\nRewriteRule ^(?:host)\b - [F,L]\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule (.*)$ ".$_SERVER['PHP_SELF']."/$1 [L,QSA]\n");

				//self::export('var-gonzo', self::var('gonzo'));
				self::var('gonzo', null, true);
			}
		}

		if (self::var('gonzo.debug') == 'on')
		{
			ini_set('html_errors', 1);
			ini_set('error_prepend_string', '<pre style="white-space: pre-line; font-size: 1em; line-height: 1.5; color: inherit;">');
			ini_set('error_append_string', '</pre>');

			error_reporting(E_ALL);
		} else
		{
			ini_set('html_errors', 0);

			error_reporting(0);
		}

		date_default_timezone_set(self::var('gonzo.timezone'));

		$url = parse_url(self::var('gonzo.url'));

		self::var('gonzo.path', (is($url, 'path') ? $url['path'] : '/'));

		self::$initialized = true;

		self::trigger('gonzo.init');
	}

	public static function find_file($path)
	{
		$path = self::filtered('gonzo.find_file', $path);
		$path = strtolower($path);

		if (file_exists(self::path($path.GONZO_EXT)))
		{
			return self::path($path.GONZO_EXT);
		}
		return false;
	}

	public static function import($path)
	{
		if (is_array($path))
		{
			foreach ($path as $single_path)
			{
				self::import($single_path);
			}

			return true;
		}

		$path = str_replace('.', GONZO_SLASH, $path);

		if (self::find_plugin($path))
		{
			include_once(self::find_plugin($path));

			return true;
		} else if (self::find_file($path))
		{
			include_once(self::find_file($path));

			return true;
		}

		return false;
	}

	public static function export($path, $data)
	{
		$tmppath = self::path($path.'~'.GONZO_EXT);
		$path = self::path($path.GONZO_EXT);

		if ($path == 'gonzo' && isset($data['path']))
		{
			unset($data['path']);
		}

		$export = '<?php return '.str_replace("\n  ", "\n\t", var_export($data, true)).';';

		if (file_put_contents($tmppath, $export, LOCK_EX) === strlen($export))
		{
			return rename($tmppath, $path);
		}

		@unlink($tmppath);
	}

	public static function var($name, $value = array(), $export = false)
	{
		$node = explode('.', $name, 2);
		$nodes = count($node);

		$group = array_shift($node);
		$field = '';

		if ($nodes > 1)
		{
			$field = implode('.', $node);
		}

		if (not(self::$var, $group))
		{
			self::$var[$group] = array();
			self::$memcache['var'][$group] = true;
		}

		if (not(self::$var, $group) || not(self::$memcache['var'], $group) || ! self::$memcache['var'][$group])
		{
			self::$memcache['var'][$group] = true;
			$path = self::find_file('var-'.$group);
			$var = array();

			if ($path !== false)
			{
				$var = include_once($path);
			} else if (is_array($value) && ! empty($value))
			{
				$var = $value;
			}

			foreach($var as $k => $v)
			{
				self::$var[$group][$k] = $v;
			}

			if (is_array($field) && empty($field))
			{
				return self::$var[$group];
			}

			if (not(self::$var, $group))
			{
				self::$var[$group] = array();
				self::$var[$group][$field] = $value;
			}
		}

		if ($value !== array())
		{
			if ($value !== null)
			{
				self::$var[$group][$field] = $value;
			}

			if ($export)
			{
				self::export('var-'.$group, self::var($group));
			}
		} else
		{
			if ( ! isset(self::$var[$group]))
			{
				self::$var[$group] = array();
			}

			if (empty($field))
			{
				return self::$var[$group];
			} else
			{
				if ( ! isset(self::$var[$group][$field]))
				{
					self::$var[$group][$field] = array();
				}

				return self::$var[$group][$field];
			}
		}
	}

	public static function lang()
	{
		$dir = path_dir(self::var('gonzo.i18n_dir'));

		if ( ! empty($dir))
		{
			dir_ensure(GONZO_PATH.$dir);
		}

		$args = func_get_args();
		$format = array_shift($args);
		$domain = array_shift($args);
		$lang = array_shift($args);

		if (empty(trim($format)))
		{
			return $format;
		}

		if ( ! isset($lang) || empty($lang))
		{
			$lang = self::var('gonzo.i18n_lang');
		}

		if ( ! isset($domain) || empty($domain))
		{
			$domain = 'gonzo';
		}

		if (not(self::$memcache['lang'], $lang))
		{
			self::$memcache['lang'][$lang] = array();
			self::$locale[$lang] = array();
		}

		if (not(self::$memcache['lang'][$lang], $domain))
		{
			self::$memcache['lang'][$lang][$domain] = array();
			self::$locale[$lang][$domain] = array();
		}

		if (not(self::$memcache['lang'][$lang], $domain) || ! self::$memcache['lang'][$lang][$domain])
		{
			$path = self::find_file($dir.(empty($domain) ? '' : $domain.'-').$lang);

			if ($path !== false)
			{
				self::$memcache['lang'][$lang][$domain] = true;

				$i18n = include_once($path);

				foreach ($i18n as $k => $v)
				{
					self::$locale[$lang][$domain][$k] = $v;
				}
			}
		}

		if (not(self::$locale[$lang][$domain], $format))
		{
			return $format;
		}

		return self::$locale[$lang][$domain][$format];
	}

	public static function job($name, $time = false)
	{
		$job = self::var('job');

		if ( ! is_array($job))
		{
			$job = array();
		}

		if ( ! isset($job[$name]))
		{
			$job[$name] = array
			(
				'time' => time_parse($schedule)
			);
		}
	}

	public static function cache($name, $data = null, $write = null)
	{
		$dir = path_dir(self::var('gonzo.cache_dir'));

		if ( ! empty($dir))
		{
			dir_ensure(GONZO_PATH.$dir);
		}

		$subcache = explode('.', $name, 2);

		if (count($subcache) == 2)
		{
			$dir .= $subcache[0].'/';
			$name = $subcache[1];

			dir_ensure(GONZO_PATH.$dir);
		}

		$path = GONZO_PATH.$dir.md5($name);

		if (self::var('gonzo.cache') == 'on' && $write === null && is_string($data))
		{
			$time = time_parse($data);

			if (is_file($path) && (filemtime($path) + $time) > time())
			{
				return true;
			}

			return false;
		} else if ($write === true)
		{
			if (self::var('gonzo.cache') == 'on')
			{
				file_put_contents($path, json_encode($data));
			}

			return $data;
		} else if ($write === false)
		{
			if (count($subcache) == 2 && is_dir(GONZO_PATH.$dir))
			{
				$files = path_scan(GONZO_PATH.$dir);

				foreach ($files as $file)
				{
					if (is_file(GONZO_PATH.$dir.$file))
					{
						unlink(GONZO_PATH.$dir.$file);
					}
				}

				return true;
			} else if (is_file($path))
			{
				return unlink($path);
			}

			return false;
		}

		if (self::var('gonzo.cache') == 'on' && is_file($path))
		{
			return json_decode(file_get_contents($path), true);
		}

		return false;
	}

	public static function http_url($url)
	{
		if (substr($url, 0, 4) != 'http')
		{
			return 'http'.(self::var('gonzo.secure') == 'on' ? 's' : '').'://'.$url;
		}

		return str_replace('http'.(self::var('gonzo.secure') == 'on' ? '' : 's').'://', 'http'.(self::var('gonzo.secure') == 'on' ? 's' : '').'://',  $url);
	}

	public static function http_rdir($path)
	{
		self::trigger('gonzo.http_rdir');

		$url = self::url($path);
		$url = self::filtered('gonzo.http_rdir.url', $url, $path);

		if (self::ajax())
		{
			header('Redirect: '.$url);
		} else
		{
			header('Location: '.$url);
		}

		exit;
	}

	public static function url($path, $absolute = true)
	{
		if (substr($path, 0, 4) == 'http')
		{
			return self::http_url($path);
		}

		$path = ltrim($path, '/');
		$url = ($absolute ? self::var('gonzo.url') : self::var('gonzo.path')).(empty($path) ? '' : GONZO_SLASH).str_replace(GONZO_PATH, '', $path);
		$url = rtrim($url, '/');

		$url = self::filtered('gonzo.url', self::http_url($url), $path, $absolute);

		return $url;
	}

	public static function path($path)
	{
		return self::filtered('gonzo.path', GONZO_PATH.path_trim($path));
	}

	public static function request_uri($url = false)
	{
		$dir = str_replace(self::var('gonzo.file'), '', isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : $_SERVER['SCRIPT_NAME']);
		$uri = explode('?', '/'.trim(preg_replace('/'.str_replace('/', '\/', $dir).'/', '', $_SERVER['REQUEST_URI'], 1), '/'));

		$uri[0] = self::filtered('gonzo.request_uri', $uri[0]);

		if ($url)
		{
			$uri[0] = self::url($uri[0]);
		}

		return $uri[0];
	}

	public static function __respond_process($content)
	{
		$m = self::var('gonzo');

		if (($m['debug'] == 'on' || $m['footnote']) && $m['content_type'] == 'text/html')
		{
			$log = '<p style="font-size: 16px; padding: 1em; text-align: center;">'.$m['lang'].' '.$m['charset'].' '.date('d/m/Y H:s', time()).' '.substr(((microtime(true) - GONZO) / 1000.0).'', 0, 4).'ms</p><div class="gonzo-log">'.self::$log.'</div>';

			if (strstr($content, '</body>'))
			{
				return str_replace('</body>', $log.'</body>', $content);
			} else
			{
				return $content.$log;
			}

			return str_replace('</body>', '</body>', $content);
		}

		return $content;
	}

	public static function respond($data)
	{
		self::trigger('gonzo.respond');

		if (is(self::$http_status, self::var('gonzo.status')))
		{
			header('HTTP/1.1 '.self::$http_status[self::var('gonzo.status')]);
		}

		header('Content-Type: '.self::var('gonzo.content_type').'; charset='.self::var('gonzo.charset'));

		$data = self::filtered('gonzo.response', $data);

		ob_start(array('gonzo', '__respond_process'));

		echo $data;

		if (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		self::trigger('gonzo.exit');

		exit;
	}

	public static function fetch($url, $data = null, $binary = false, $headers = true)
	{
		$c = curl_init();

		curl_setopt($c, CURLOPT_URL, $url);

		curl_setopt_array($c, array
		(
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
			CURLOPT_ENCODING =>'gzip, deflate',
			CURLOPT_HTTPHEADER => array
			(
				'Accept-Language: en-US,en;q=0.5',
				'Accept-Encoding: utf-8,gzip,deflate',
				'Connection: keep-alive',
				'Upgrade-Insecure-Requests: 1'
			)
		));

		if ($data && ! empty($data))
		{
			$url_data = '';

			foreach ($data as $k => $v)
			{
				if ( ! empty($url_data))
				{
					$url_data .= '&';
				}

				$url_data .= $k.'='.$v;
			}

			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $url_data);
		}

		$purl = parse_url($url);
		$referer = (isset($purl['scheme']) ? $purl['scheme'] : 'http').'://'.$purl['host'].'/';

		curl_setopt($c, CURLOPT_REFERER, $referer);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);

		if ($binary)
		{
			curl_setopt($c, CURLOPT_BINARYTRANSFER, 1);
		}

		curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 15);
		curl_setopt($c, CURLOPT_FAILONERROR, 1);
		curl_setopt($c, CURLOPT_ENCODING, '');
		curl_setopt($c, CURLOPT_NOPROGRESS, 0);
		// requires php 5.5.0
		curl_setopt($c, CURLOPT_PROGRESSFUNCTION, function($c, $download_size, $downloaded, $upload_size, $uploaded)
		{
			$limit = (8 * 1024 * 1024);

			return ($download_size > $limit) ? 1 : 0;
		});

		$content = curl_exec($c);

		$mime = '';
		$base_url = '';

		if ($errno = curl_errno($c))
		{
			$error = curl_strerror($errno);
			$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

			curl_close($c);

			if ($errno == 42)
			{
				$error = 'Response exceeds file size limit';
			}

			return array('error' => $error, 'errno' => $errno, 'status' => $status);
		} else
		{
			$mime = explode(';', curl_getinfo($c, CURLINFO_CONTENT_TYPE));
			$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

			if (isset($mime[0]))
			{
				$mime = $mime[0];
			} else
			{
				$mime = false;
			}
		}

		curl_close($c);

		return array('mime' => $mime, 'status' => $status, 'content' => $content);
	}

	// template files are prefixed with _ so they can be easly recognized among other php scripts/utilities
	public static function find_view($name)
	{
		$dir = path_dir(self::var('gonzo.view_dir'));

		$path = '';
		$view = '';

		$names = explode(',', $name);

		for ($i = 0; $i < count($names); $i++)
		{
			$names[$i] = self::filtered('gonzo.view_name', $names[$i]);

			$dirname = path_dir($names[$i], true);
			$ext = path_ext($names[$i]);
			$basename = '_'.path_base($names[$i]);

			$plugin_path = self::find_plugin($dirname.$basename, false);

			if (file_exists($plugin_path))
			{
				return $plugin_path;
			}

			$path = self::find_file($dir.$dirname.$basename);

			if (file_exists($path))
			{
				return $path;
			}
		}

		return $view;
	}

	public static function view($name, $data = array())
	{
		$dir = path_dir(self::var('gonzo.view_dir'));

		if ( ! empty($dir))
		{
			dir_ensure(GONZO_PATH.$dir);
		}

		self::trigger('view.parse_before');

		// empty name indicates plain response without templates
		if (empty($name))
		{
			return ( ! is_array($data) ? $data : self::var('gonzo.content'));
		}

		$path =  self::find_view($name);
		$ext = path_ext($name);

		if ( ! file_exists($path))
		{
			return false;
		}

		if ( ! empty($ext) && isset(self::$http_mime[ltrim($ext, '.')]))
		{
			self::var('gonzo.content_type', self::$http_mime[ltrim($ext, '.')]);
		}

		self::trigger('view.parse');

		if (is_array($data))
		{
			foreach ($data as $k => $v)
			{
				self::var('view.'.$k, $v);
			}
		}

		if (isset($_SESSION))
		{
			foreach ($_SESSION as $key => $value)
			{
				self::var('view.session.'.$key, $value);
			}
		}

		$globals = self::var('view');

		foreach ($globals as $k => $v)
		{
			global $$k;

			$$k = $v;
		}

		$dir = path_dir($name, true);
		$ext = path_ext($name);

		$head = '_head'.$ext;
		$foot = '_foot'.$ext;

		$head_path = self::find_view($dir.$head);
		$foot_path = self::find_view($dir.$foot);

		if ( ! file_exists($head_path))
		{
			$head_path = self::find_view($head);
		}

		if ( ! file_exists($foot_path))
		{
			$foot_path = self::find_view($foot);
		}

		ob_start();
		include($path);
		$view_content = ob_get_clean();

		ob_start();

		if (file_exists($head_path))
		{
			include($head_path);
		}

		$head_content = ob_get_clean();

		ob_start();

		if (file_exists($foot_path))
		{
			include($foot_path);
		}

		$foot_content = ob_get_clean();

		self::trigger('view.parse_after');

		return $head_content.$view_content.$foot_content;
	}

	public static function dispatch($request_path)
	{
		self::trigger('gonzo.dispatch');

		$request_result = '';
		$part_match = '(\w(?:[(-.)\w]*\w)?)';

		foreach (self::$routes as $route => $route_data)
		{
			$vars = array();
			$rule = ltrim($route, '!');

			if (preg_match_all('/:'.$part_match.'/', $rule, $rule_vars))
			{
				foreach ($rule_vars[0] as $rule_var)
				{
					$rule = str_replace($rule_var, $part_match, $rule);
					array_push($vars, trim($rule_var, ':'));
				}
			}

			$rule = str_replace(array('/'), array('\/'), $rule);
			$rule = '^'.$rule.'\/?$';

			if (preg_match('/'.$rule.'/i', $request_path, $matches))
			{
				$data = array('route' => $rule);

				array_shift($matches);
				$count = count($matches);

				for ($i = 0; $i < $count; $i++)
				{
					$data[$vars[$i]] = $matches[$i];
				}

				if (isset($route_data['method']) && ! is_array($route_data['method']))
				{
					$route_data['method'] = array($route_data['method']);
				}

				if (not($route_data, 'method') || is($_SERVER, 'REQUEST_METHOD', $route_data['method']))
				{
					$request_result .= self::trigger($route, $data);

					if ( ! empty($request_result))
					{
						break;
					}
				}
			}
		}

		if ( ! $request_result && self::var('gonzo.auto_route') == 'on')
		{
			$name = ltrim($request_path, '/');

			if (substr($name, 0, 1) != '_')
			{
				if (empty($name))
				{
					$name = 'index';
				}

				if ( ! empty($name))
				{
					$path = self::find_view($name);

					if (file_exists($path))
					{
						return self::view($name);
					}
				}
			}
		}

		return $request_result;
	}

	public static function run($plugins = array())
	{
		if ( ! self::$initialized)
		{
			self::init();
		}

		self::trigger('gonzo.run');

		$plugins_path = self::path(self::var('gonzo.plugin_dir'));
		$all_plugins = array_diff(scandir($plugins_path), array('..', '.'));

		$plugins = self::filtered('gonzo.plugins', $plugins);

		foreach ($plugins as $plugin)
		{
			$plugin_path = $plugins_path.GONZO_SLASH.$plugin.GONZO_SLASH.$plugin.GONZO_EXT;

			if ( ! empty($plugin) && file_exists($plugin_path))
			{
				self::$plugins[$plugin] = $plugin_path;
			}
		}

		foreach (self::$plugins as $plugin => $plugin_path)
		{
			self::trigger('plugin.'.$plugin);

			require_once($plugin_path);
		}

		$request_result = self::dispatch(self::request_uri());

		if (empty($request_result))
		{
			self::log('error.404', self::$http_status[404]);
		} else
		{
			self::respond($request_result);
		}
	}

	public static function find_plugin($path, $find_file_fallback = true)
	{
		foreach (self::$plugins as $plugin => $plugin_path)
		{
			$plugin = strtolower($plugin);

			$plugin_file = dirname($plugin_path).GONZO_SLASH.path_trim($path).GONZO_EXT;

			if (file_exists($plugin_file))
			{
				return $plugin_file;
			}
		}

		return ($find_file_fallback ? self::find_file($path) : false);
	}

	public static function instance($class, $instance_name = 'gonzo')
	{
		$class = ucwords(str_replace('-', '_', $class), '_');

		if (not(self::$instances, $class))
		{
			self::$instances[$class] = array('class' => $class, 'instances' => array());
		}

		if (in(self::$instances[$class]['instances'], $instance_name))
		{
			return self::$instances[$class]['instances'][$instance_name];
		}

		if ( ! class_exists($class, false))
		{
			self::import(strtolower(str_replace('_', '-', $class)));

			if ( ! class_exists($class, false))
			{

				return false;
			}
		}

		self::$instances[$class]['instances'][$instance_name] = new self::$instances[$class]['class'];

		return self::$instances[$class]['instances'][$instance_name];
	}

	public static function instanceof($class, $new_class)
	{
		$class = ucwords(str_replace('-', '_', $class), '_');
		$new_class = ucwords(str_replace('-', '_', $new_class), '_');

		if ( ! class_exists($new_class, false))
		{
			self::import(strtolower(str_replace('_', '-', $new_class)));

			if ( ! class_exists($new_class, false))
			{
				return false;
			}
		}

		if (not(self::$instances, $class))
		{
			self::$instances[$class] = array('class' => $class, 'instances' => array());
		}

		self::$instances[$class]['class'] = $new_class;

		return true;
	}

	public static function input($type = 'any', $field = null)
	{
	 	$input = array();

		switch ($type)
		{
			case 'get':
				$input = isset($_GET) ? $_GET : false;
			break;
			case 'post':
				$input = isset($_POST) ? $_POST : false;
			break;
			default:
				$input = isset($_REQUEST) ? $_REQUEST : false;
			break;
		}

		if ( ! $input || $field === null)
		{
			return $input;
		}

		$input = self::filtered('gonzo.input', $input);

		$fields = explode('.', $field);

		if (count($fields) > 1)
		{
			foreach ($fields as $field)
			{
				if (isset($input[$field]))
				{
					$input = $input[$field];
				} else
				{
					return null;
				}
			}

			return $input;
		}

		return isset($input[$field]) ? $input[$field] : null;
	}

	public static function ajax()
	{
		return (is($_SERVER, 'HTTP_X_REQUESTED_WITH', 'XMLHttpRequest'));
	}

	public static function local()
	{
		$pos = isset($_SERVER['HTTP_REFERER']) && stripos($_SERVER['HTTP_REFERER'], self::var('gonzo.url'));

		return $pos !== false;
	}

	public static function auth($user, $password)
	{
		if (not($_SERVER, 'PHP_AUTH_USER', $user) || not($_SERVER, 'PHP_AUTH_PW', $password))
		{
			header('WWW-Authenticate: Basic realm="'.self::var('gonzo.name').'"');
			header('Status: 401 Unauthorized');

			return false;
		}

		return true;
	}

	public static function highlight($code, $lang = '')
	{
		switch ($lang)
		{
			case 'html':
			{
				ini_set('highlight.comment', 'green');
				ini_set('highlight.default', '#CC0000');
				ini_set('highlight.html', '#000000');
				ini_set('highlight.keyword', '#000000; font-weight: bold');
				ini_set('highlight.string', '#0000FF');
			}
			break;
			case 'php':
			{
				ini_set('highlight.comment', '#008000');
				ini_set('highlight.default', '#000000');
				ini_set('highlight.html', '#808080');
				ini_set('highlight.keyword', '#0000BB; font-weight: bold');
				ini_set('highlight.string', '#DD0000');
			}
			break;
			case 'text':
			{
				ini_set('highlight.comment', '#C0C0C0 font-style: italic');
				ini_set('highlight.default', '#171819;');
				ini_set('highlight.html', '#171819');
				ini_set('highlight.keyword', '#999999; font-weight: bold');
				ini_set('highlight.string', '#666666; font-style: italic; font-weight: bold');
			}
			break;
			default:
			{
				ini_set('highlight.comment', '#C0C0C0 font-style: italic');
				ini_set('highlight.default', '#171819;');
				ini_set('highlight.html', '#AAAAAA');
				ini_set('highlight.keyword', '#999999; font-weight: bold');
				ini_set('highlight.string', '#666666; font-style: italic; font-weight: bold');
			}
			break;
		}

		$code = trim($code);

		if (version_compare(PHP_VERSION, '4.2.0', '<') === 1)
		{
			ob_start();
			highlight_string('<?php '.$code);

			$code = ob_get_contents();

			ob_end_clean();
		} else
		{
			$code = highlight_string('<?php '.$code, true);
		}

		$head = '';
		$trim = '&lt;?php&nbsp;';

		$code = trim($code);
		$code = explode($trim, $code);

		if (count($code) > 1)
		{
			$head = array_shift($code);
		}

		$code = $head.implode($trim, $code);

		$code = preg_replace('#<font color="([^\']*)">([^\']*)</font>#', '<span style="color: \\1">\\2</span>', $code);
		$code = preg_replace('#<font color="([^\']*)">([^\']*)</font>#U', '<span style="color: \\1">\\2</span>', $code);

		return '<pre style="white-space: pre-wrap; word-wrap: break-word; overflow: auto; margin-top: 0">'.$code.'</pre>';
	}

	public static function log($type, $info = '', $context = null)
	{
		$error = substr($type, 0, 5) == 'error';
		$message = '';

		if ($error)
		{
			$overwrite = self::trigger($type);

			if ($overwrite !== null)
			{
				self::respond($overwrite);
			}

			$message .= ' <span style="font-weight: bold;">'.self::lang('Error').'</span>';
		}

		switch ($type)
		{
			case 'error.db.connect':
				$message .= ': '.self::lang('Could not connect to database');
			break;

			case 'error.db.database':
				$message .= ': '.self::lang('Could not select database');
			break;

			case 'error.db.query':
				$message .= ': '.self::lang('Could not execute query');
			break;

			case 'error.fatal':
				$message .= ': '.self::lang('Fatal error');
			break;
			default:
				if (isset(self::$http_status[$type]))
				{
					$message .= self::$http_status[$type];
				}
			break;
		}

		$backtrace = debug_backtrace();

		if (self::var('gonzo.debug') == 'on')
		{
			$message .= ' in '.$backtrace[1]['file'].' on line '.$backtrace[1]['line'];
		}

		if ( ! empty($message))
		{
			$message = '<div>'.$message.'</div>';
		}

		if (self::var('gonzo.debug') == 'on')
		{
			if ( ! empty($info))
			{
				if (is_string($info))
				{
					$message .= '<div>&nbsp;&nbsp;'.self::highlight($info).'</div>';
				} else
				{
					$message .= self::highlight(var_export($info, true));
				}
			}

			if ($error)
			{
				if ($type == 'error.db.query' && $context)
				{
					$message .= '<div>&nbsp;&nbsp;'.self::highlight('#'.$context->error_number().' '.$context->error_message(), 'text').'</div>';
				}

				if ( ! empty($context))
				{
					$message .= '<div style="height: 1.5em; overflow: hidden" onclick="this.firstChild.remove(); this.style=\'\'"><div>&nbsp;&nbsp;<strong>$context</strong></div>'.self::highlight(var_export($context, true)).'</div>';
				}

				$message .= '';
				$message .= '<div style="height: 1.5em; overflow: hidden" onclick="this.firstChild.remove(); this.style=\'\'"><div>&nbsp;&nbsp;<strong>debug_backtrace()</strong></div>'.self::highlight(var_export($backtrace, true)).'</div>';
			}
		}

		self::$log .= $message;

		return false;
	}
}

if ( ! function_exists('view'))
{
	function view($name)
	{
		if ( ! empty($name))
		{
			$path = gonzo::find_view($name);

			if ( ! empty($path) && file_exists($path))
			{
				return include($path);
			}
		}

		return '';
	}
}

if ( ! function_exists('__'))
{
	function __()
	{
		$__args = func_get_args();

		$format = array_shift($__args);

		$args = array();

		$domain = gonzo::var('gonzo.i18n_domain');

		if (count($__args) > 0)
		{
			$__domain = array_shift($__args);

			if (is_string($__domain))
			{
				$domain = $__domain;
			}
		}

		if (count($__args) > 0)
		{
			$args = array_shift($__args);

			if ( ! is_array($args))
			{
				$args = array($args);
			}
		}

		$lang = gonzo::var('gonzo.i18n_lang');

		return vsprintf(gonzo::lang($format, $domain, $lang), $args);
	}
}

if ( ! function_exists('_e'))
{
	function _e()
	{
		$args = func_get_args();

		echo call_user_func_array('__', $args);
	}
}

gonzo::setup();
