<?php

gonzo::import('filter');

function sql_operator(&$column)
{
	$operators = '<=|>=|<|>|!=|=|is null|is not null|like|not like|in|not in|between|not between';

	preg_match('/\s('.$operators.')/i', $column, $match);

	if (is($match, 0))
	{
		$column = str_replace($match[0], '', $column);

		return $match[0];
	}

	return '';
}

function sql_relation(&$column, $default = '')
{
	if (substr($column, 0, 2) == 'or')
	{
		$column = ltrim(substr($column, 2));

		return ' or ';
	} else if (substr($column, 0, 3) == 'and')
	{
		$column = ltrim(substr($column, 3));

		return ' and ';
	}

	return ( ! empty($default) ? ' '.$default.' ' : $default);
}

function sql_fields($fields, $escape = '\'', $separator = ',', $key_prefix = '')
{
	if (is_string($fields) && ! stristr($fields, ','))
	{
		if ($fields == '*' || $fields == '(' || $fields == ')')
		{
			return $fields;
		}

		$parts = explode('.', $fields);
		$parts[0] = $escape.$parts[0].$escape;

		if (is($parts, 1))
		{
			$parts[1] = $parts[1] == '*' ? '*' : $escape.$key_prefix.$parts[1].$escape;
		}

		return str_replace('%%', gonzo::var('db.prefix'), filter_santize(implode('.', $parts)));
	} else if ( ! is_array($fields))
	{
		$fields = explode(',', $fields);

		array_unshift($fields, '(');
		array_push($fields, ')');
	}

	if (is_assoc($fields))
	{
		$first = true;
		$cols = array();

		foreach ($fields as $col => $val)
		{
			if ($col == '(' || $col == ')')
			{
				$cols[] = $col;

				continue;
			}

			$relation = '';

			if ( ! $first)
			{
				$relation = sql_relation($col, (($separator == 'or' || $separator == 'and')) ? $separator : '');
			}

			$operator = sql_operator($col);

			if ($escape == '`')
			{
				$col = '`'.str_replace('.', '`.`', $col).'`';
			}

			if (is_array($val))
			{
				$val = '('.sql_fields($val, '\'').')';
			} else if ( ! is_numeric($val))
			{
				if (substr($val, 0, 1) == '&')
				{
					$val = ltrim($val, '&');
				} else
				{
					$val = '\''.filter_santize($val).'\'';
				}
			}

			$cols[] = ( ! empty($relation) ? ' '.$relation.' ' : '').$key_prefix.$col.(empty($operator) ? $escape : ' '.trim($operator).' ').$val;
			$first = false;
		}

		return str_replace('%%', gonzo::var('db.prefix'), implode((($separator == 'or' || $separator == 'and' || ! empty($relation)) ? '' : $separator), $cols));
	} else
	{
		for ($i = 0; $i < count($fields); $i++)
		{
			if (is_array($fields[$i]) && is_assoc($fields[$i]))
			{
				$fields[$i] = '('.sql_fields($fields[$i], $escape, 'and', $key_prefix).')';
			} else if ($fields[$i] == '(' || $fields[$i] == ')')
			{
				continue;
			} if (substr($fields[$i], 0, 1) == '&')
			{
				$fields[$i] = ltrim($fields[$i], '&');
			} else if ($escape == '\'' || $escape == '"' || $escape == '`')
			{
				$fields[$i] = (is_numeric($fields[$i]) ? $fields[$i] : $escape.filter_santize($fields[$i]).$escape);
			} else
			{
				$as = false;
				$alias = '';

				if (stristr($fields[$i], ' as '))
				{
					$parts = explode(' as ', $fields[$i]);
					$as = true;

					$fields[$i] = $escape.trim(str_replace('.', $escape.'.'.$escape, $parts[0])).$escape.' AS '.$escape.trim($parts[1]).$escape;
				}
			}
		}
	}

	return str_replace(array('%%', '('.$separator, $separator.')'), array(gonzo::var('db.prefix'), '(', ')'), implode($separator, $fields));
}

function sql_filter()
{
	$args = func_get_args();

	$args[0] = str_replace('%%', gonzo::var('db.prefix'), $args[0]);

	return call_user_func_array('sprintf', $args);
}
