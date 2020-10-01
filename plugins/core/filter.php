<?php
function filter_ascii($text)
{
	$text = preg_replace('/[^\x20-\x7E]*/', '', $text);

	return $text;
}

function filter_html_strip($text, $valid_tags = null)
{
	$regexp = '#\s*<(/?\w+)\s+(?:on\w+\s*=\s*(["\'\s])?.+?\(\1?.+?\1?\);?\1?|style=["\'].+?["\'])\s*>#is';

	return preg_replace($regexp, '<${1}>', strip_tags($text, $valid_tags));
}

function filter_html_encode($text)
{
	return htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8');
}

function filter_html_decode($code)
{
	return html_entity_decode($code, ENT_NOQUOTES);
}

function filter_strip_slashes($text)
{
	if (is_numeric($text))
	{
		return $text;
	}

	$pattern = array('\\\'', '\\"', '\\\\', '\\0');
	$replace = array('\'', '"', '', '');

	return str_replace($pattern, $replace, $text);
}

function filter_add_slashes($text)
{
	$pattern = array('\\\'', '\\"', '\\\\', '\\0');
	$replace = array('', '', '', '');

	if (preg_match('/[\\\\\'"\\0]/', str_replace($pattern, $replace, $text)))
	{
		return addslashes($text);
	}

	return $text;
}

function filter_santize($text)
{
	$text = filter_add_slashes($text);
	$text = filter_html_strip($text);

	return $text;
}

function filter_slug($string, $delimiters = '\/_|+ -')
{
	$string = strip_tags($string);
	$string = preg_replace('/[^a-zA-Z0-9'.$delimiters.']/', '', $string);
	$string = strtolower(trim($string, '-'));
	$string = str_replace('/', '-', $string);
	$string = preg_replace('/[_|+ -]+/', '-', $string);
	$string = preg_replace('/(\/)+/', '/', $string);

	return rtrim($string, $delimiters);
}

function filter_word_count($string, $length = 100, $append = '&hellip;')
{
	if (strlen($string) <= $length)
	{
		return $string;
	}

	$string = substr($string, 0, $length);

	if (strpos($string, ' ') === false)
	{
		return $string.$append;
	}

	return preg_replace('/\w+$/', '', $string).$append;
}

function filter_bytes($number)
{
	$treshold = array('K', 'M', 'G', 'T');
	$round = '';

	while ($number >= 1000 && count($treshold) > 0)
	{
		$number = $number / 1000.0;
		$round = array_shift($treshold);
	}

	if (empty($round))
	{
		$round = 'b';
	}

	return round($number, max(0, 3 - strlen((int)$number))).$round;
}

function filter_hash($text)
{
	return md5(gonzo::var('gonzo.salt').$text);
}

function filter_is_json($text)
{
	return is_string($text) && preg_match('/^[\[\{]\"/', $text);
}

function filter_json_encode($text)
{
	if (is_array($text) && ! filter_is_json($text))
	{
		return json_encode($text, JSON_FORCE_OBJECT);
	}

	return $text;
}

function filter_json_decode($text)
{
	if ( ! is_array($text) && filter_is_json($text))
	{
		return json_decode($text);
	}

	return $text;
}

function filter_alphaid_encode($text, $count = FALSE, $key = NULL, $reverse = FALSE)
{
	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	if ($key !== NULL)
	{
		for ($n = 0; $n < strlen($chars); $n++)
		{
			$i[] = substr($chars, $n, 1);
		}

		$hash = hash('sha256', $key);
		$hash = (strlen($hash) < strlen($chars)) ? hash('sha512', $key) : $hash;

		for ($n = 0; $n < strlen($chars); $n++)
		{
			$p[] =  substr($hash, $n ,1);
		}

		array_multisort($p,  SORT_DESC, $i);
		$chars = implode($i);
	}

	$base = strlen($chars);

	if ($reverse)
	{
		$text  = strrev($text);
		$id = 0;
		$len = strlen($text) - 1;

		for ($t = 0; $t <= $len; $t++)
		{
			$bcpow = pow($base, $len - $t);
			$id   = $id + strpos($chars, substr($text, $t, 1)) * $bcpow;
		}

		if (is_numeric($count))
		{
			$count--;

			if ($count > 0)
			{
				$id -= pow($base, $count);
			}
		}

		$id = sprintf('%F', $id);
		$id = substr($id, 0, strpos($id, '.'));
	} else
	{
		if (is_numeric($count))
		{
			$count--;

			if ($count > 0)
			{
				$text += pow($base, $count);
			}
		}

		$id = '';

		for ($t = floor(log($text, $base)); $t >= 0; $t--)
		{
			$bcp = pow($base, $t);
			$a   = floor($text / $bcp) % $base;
			$id = $id . substr($chars, $a, 1);
			$text  = $text - ($a * $bcp);
		}

		$id = strrev($id);
	}

	return $id;
}

function filter_alphaid_decode($text, $count = FALSE, $key = NULL)
{
	return filter_alphaid_encode($text, $count, $key, true);
}

function filter_word_wrap($text, $words = 40)
{

}

function filter_length($text, $length = 80, $append = '&hellip;')
{
	if (strlen($text) <= $length)
	{
		return $text;
	}

	$text = substr($text, 0, $length);

	if (strpos($text, ' ') === FALSE)
	{
		return $text.$append;
	}

	return preg_replace('/\w+$/', '', $text).$append;
}

function filter_random($length = 8, $use_upper = true, $use_lower = true, $use_number = true, $use_custom = '')
{
	$upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$lower = 'abcdefghijklmnopqrstuvwxyz';
	$number = '0123456789';

	$seed_length = 0;
	$seed = '';

	if ($use_upper)
	{
		$seed_length += 26;
		$seed .= $upper;
	}

	if ($use_lower)
	{
		$seed_length += 26;
		$seed .= $lower;
	}

	if ($use_number)
	{
		$seed_length += 10;
		$seed .= $number;
	}

	if ($use_custom)
	{
		$seed_length += strlen($use_custom);
		$seed .= $use_custom;
	}

	$text = '';

	for($i = 1; $i <= $length; $i++)
	{
		$text .= $seed[rand(0, $seed_length - 1)];
	}

	return $text;
}

function filter_time_format($time)
{
	return date('d.m.Y', $time);
}

function filter_time_parse($time)
{
	$timestamp = 'YMdhms';
	$counter = array
	(
		'Y' => 1 * 60 * 60 * 24 * 31 * 12,
		'M' => 1 * 60 * 60 * 24 * 31,
		'd' => 1 * 60 * 60* 24,
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

function filter_time_ago($time)
{
	$i = array(60, 60 * 60, 24 * 60 * 60, 30 * 24 * 60 * 60, 12 * 30 * 24 * 60 * 60);

	$ago = '';
	$diff = time() - $time;

	if ($diff <= 29)
	{
		$ago = 'less than a minute';
	} else if ($diff > 29 && $diff <= 89)
	{
		$ago = '1 minute';
	} else if ($diff > 89 && $diff <= ($i[0] * 44) + 29)
	{
		$minutes = floor($diff / $i[0]);
		$ago = $minutes.' minutes';
	} else if ($diff > ($i[0] * 44) + 29 && $diff < ($i[0] * 89) + 29)
	{
		$ago = 'about 1 hour';
	} else if ($diff > ($i[0] * 89) + 29 && $diff <= ($i[1] * 23) + ($i[0] * 59) + 29)
	{
		$hours = floor($diff / $i[1]);
		$ago = $hours.' hours';
	} else if ($diff > ($i[1] * 23) + ($i[0] * 59) + 29 && $diff <= ($i[1] * 47) + ($i[0] * 59) + 29)
	{
		$ago = '1 day';
	} else if ($diff > ($i[1] * 47) + ($i[0] * 59) + 29 && $diff <= ($i[2] * 29) + ($i[1] * 23) + ($i[0] * 59) + 29)
	{
		$days = floor($diff / $i[2]);
		$ago = $days.' days';
	} else if ($diff > ($i[2] * 29) + ($i[1] * 23) + ($i[0] * 59) + 29 && $diff <= ($i[2] * 59) + ($i[1] * 23) + ($i[0] * 59) + 29)
	{
		$ago = 'about 1 month';
	} else if ($diff > ($i[2] * 59) + ($i[1] * 23) + ($i[0] * 59) + 29 && $diff < $i[4])
	{
		$months = round($diff / $i[3]);

		if($months == 1)
		{
			$months = 2;
		}

		$ago = $months.' months';
	} else if ($diff >= $i[4] && $diff < $i[4] * 2)
	{
		$ago = 'about 1 year';
	} else
	{
		$years = floor($diff / $i[4]);
		$ago = 'over '.$years.' years';
	}

	return $ago.' ago';
}
