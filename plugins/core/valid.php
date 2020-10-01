<?php

gonzo::import('filter');

function valid_email($email)
{
	return (preg_match('/[-a-zA-Z0-9_.+]+@[a-zA-Z0-9-]{2,}\.[a-zA-Z]{2,}/', $email) > 0) ? true : false;
}

function valid_url($url)
{
	return ( ! preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $url)) ? false : true;
}

function valid_numeric($num)
{
	return is_numeric($num);
}

function valid_between($num, $low, $high)
{
	return (valid_numeric($num) && $num >= $low && $num <= $high);
}

function valid_alpha($content)
{
	return ctype_alpha($content);
}

function valid_alpha_numeric($content)
{
	return ctype_alnum($content);
}

function valid_lower_case($content)
{
	return (strtolower($content) == $content);
}

function valid_upper_case($content)
{
	return (strtoupper($content) == $content);
}

function valid_zip_code($zip_code)
{
	return preg_match('/^([0-9]{5})(-[0-9]{4})?$/i', $zip_code);
}

function valid_phone($phone)
{
	return preg_match('/^[\(]?[0-9]{3}[\)]?[-. ]?[0-9]{3}[-. ]?[0-9]{4}$/', $phone);
}

function valid_date($date, $format = 'DD-MM-YYYY')
{
	$format = explode('-', $format);
	$nodes = count($format);
	$exp;

	for ($i = 0; $i < $nodes; $i++)
	{
		$exp .= '([0-9]{'.strlen($format[$i]).'})';

		if ($i < $nodes-1)
		{
			$exp .= '-';
		}
	}

	if (preg_match('/^'.$exp.'$/', $date, $parts))
	{
		return true;
	}

	return false;
}

function valid_min_length($content, $length)
{
	return (strlen($content) >= $length);
}

function valid_max_length($content, $length)
{
	return (strlen($content) <= $length);
}

function valid_length($content, $length)
{
	return (strlen($content) == $length);
}

function valid_match($content, $content_match)
{
	return ($content === $content_match);
}

function valid_regexp($content, $regexp)
{
	return preg_match($regexp, $content);
}

function valid_required($content)
{
	return ( ! empty($content) || $content == '0');
}

function valid_required_numeric($content)
{
	return ( ! empty($content));
}

function valid_min($content, $min)
{
	return (valid_numeric($content) && $content >= $min);
}

function valid_max($content, $max)
{
	return (valid_numeric($content) && $content <= $max);
}

function valid_one_of($content, array $matches)
{
	foreach ($matches as $match)
	{
		if ($content == $match)
		{
			return true;
		}
	}

	return false;
}

function valid_not_one_of($content, array $matches)
{
	return empty($content) || ! valid_one_of($content, $matches);
}

function valid_format_to_number($matches)
{
	return '([0-9]{'.strlen($matches[0]).'})';
}

function valid_format_to_alpha($matches)
{
	return '([a-zA-Z]{'.strlen($matches[0]).'})';
}

function valid_format_to_regexp($format)
{
	$format = preg_quote($format);

	$format = str_replace('\(', '(', $format);
	$format = str_replace('\)', ')?', $format);

	$format = preg_replace_callback('/(\w){1,30}/', array('valid', 'format_to_number'), $format);
	$format = preg_replace_callback('/[aA]{1,30}/', array('valid', 'format_to_alpha'), $format);

	$format = str_replace(' ', '( )*', $format);

	return '/^'.$format.'$/';
}

function valid_format($content, $format)
{
	$parts = explode('|', $format);
	$count = count($parts);

	for ($i = 0; $i < $count; $i++)
	{
		$part = trim($parts[$i]);

		if (preg_match(valid_format_to_regexp($part), $content))
		{
			return true;
		}
	}

	return false;
}

function valid_error($name, $error, $context = '')
{
	$errors = gonzo::var('valid.errors');

	if ( ! is_array($errors))
	{
		$errors = array();
	}

	if ( ! isset($errors[$context]))
	{
		$errors[$context] = array();
	}

	if ( ! isset($errors[$context][$name]))
	{
		$errors[$context][$name] = array();
	}

	$errors[$context][$name][] = str_replace(':name', __($name, 'validator'), __($error, 'validator'));

	gonzo::var('valid.errors', $errors);

	return $errors[$context];
}

function valid_render($name, $context = '', $flush_errors = false)
{
	$errors = gonzo::var('valid.errors');
	$html = '';

	if (isset($errors[$context]))
	{
		if (empty($name) || $name == '*')
		{
			foreach ($errors[$context] as $field => $field_errors)
			{
				foreach ($field_errors as $index => $error)
				{
					$html .= '<pre class="error" style="color: red;">'.$error.'</pre>';
				}

				if ( ! empty($context) && $flush_errors)
				{
					unset($errors[$context][$field]);
				}
			}

			if ( ! empty($context) && $flush_errors)
			{
				gonzo::var('valid.errors', $errors);
			}
		} else if (isset($errors[$context][$name]))
		{
			foreach ($errors[$context][$name] as $error)
			{
				$html .= '<pre class="error" style="color: red;">'.$error.'</pre>';
			}

			if ( ! empty($context) && $flush_errors)
			{
				unset($errors[$context][$name]);

				gonzo::var('valid.errors', $errors);
			}
		}
	}

	return $html;
}

function validate(&$input, $ruleset, $context = '')
{
	$errors = array();

	foreach ($ruleset as $field => $rules)
	{
		if (isset($rules['filter']) && ! is_array($rules['filter']))
		{
			$rules['filter'] = explode(',', $rules['filter']);
			$ruleset[$field]['filter'] = $rules['filter'];
		}

		if ($field == '*')
		{
			continue;
		} else if ( ! isset($input[$field]))
		{
			$input[$field] = '';
		}

		$errors[$field] = array();

		if ( ! isset($rules['filter']))
		{
			$rules['filter'] = array();
		}

		if (isset($ruleset['*']) && isset($ruleset['*']['filter']))
		{
			$rules['filter'] = array_unique(array_merge(( ! is_array($ruleset['*']['filter']) ? explode(',', $ruleset['*']['filter']) : $ruleset['*']['filter']), $rules['filter']));
		}

		foreach ($rules as $rule_type => $rule)
		{
			switch ($rule_type)
			{
				case 'filter':
					foreach ($rule as $filter)
					{
						$input[$field] = call_user_func_array('filter_'.trim($filter), array($input[$field]));
					}
				break;
				case 'callback':
					$callback = array_shift($rule);
					$args = $rule;
					array_unshift($args, $input[$field]);

					if ( ! call_user_func_array($callback, $args))
					{
						$errors[$field][] = str_replace(':name', __($field, 'validator'), __('validator.callback'.((isset($args[1]) && is_string($args[1])) ? '.'.$args[1] : ((isset($args[1][1]) && is_string($args[1][1])) ? '.'.$args[1][1] : '')), 'validator', $args));
					}
				break;
				case 'match':
					$args = array($input[$field], $input[$rule]);

					if ( ! call_user_func_array('valid_match', $args))
					{
						array_shift($args);

						$errors[$field][] = str_replace(array(':name', ':match'), array(__($field, 'validator'), __($rule, 'validator')), __('validator.match.'.$field, 'validator', $args));
					}
				break;
				default:
					$args = $rule;

					if ( ! is_array($args))
					{
						$args = array($args);
					}

					array_unshift($args, $input[$field]);

					if ( ! call_user_func_array('valid_'.$rule_type, $args))
					{
						array_shift($args);

						$errors[$field][] = str_replace(':name', __($field, 'validator'), __('validator.'.$rule_type, 'validator', $args));
					}
				break;
			}
		}

		if (count($errors[$field]) == 0)
		{
			unset($errors[$field]);
		}
	}

	if ( ! empty($context))
	{
		$valid_errors = gonzo::var('valid.errors');

		if ( ! is_array($valid_errors))
		{
			$valid_errors = array();
		}

		if ( ! isset($valid_errors[$context]))
		{
			$valid_errors[$context] = array();
		}

		foreach ($errors as $key => $value)
		{
			if ( ! isset($valid_errors[$context][$key]))
			{
				$valid_errors[$context][$key] = array();
			}

			$valid_errors[$context][$key] = array_merge($errors[$key], $valid_errors[$context][$key]);
		}

		gonzo::var('valid.errors', $valid_errors);
	}

	return $errors;
}
