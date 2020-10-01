<?php

gonzo::import('functions');
gonzo::import('database-mysqli');

// this module needs more love

function table_row_group(array $data, $key, $merge_fields = array())
{
	$output = array();

	foreach ($data as $entry)
	{
		if ( ! isset($output[$entry[$key]]))
		{
			foreach ($merge_fields as $field)
			{
				if (isset($entry[$field]) && ! is_array($entry[$field]))
				{
					$entry[$field] = array($entry[$field]);
				}
			}

			$output[$entry[$key]] = $entry;
		} else
		{
			foreach ($merge_fields as $field)
			{
				if (isset($entry[$field]))
				{
					$output[$entry[$key]][$field][] = $entry[$field];
				}
			}
		}
	}

	return $output;
}

function table_row_combine(array $data, $fields)
{
	$output = array();
	$first_column = $fields[0];

	if (isset($data[$first_column]))
	{
		foreach ($data[$first_column] as $index => $value)
		{
			$entry = array();

			foreach ($fields as $field)
			{
				$entry[$field] = $data[$field][$index];
			}

			$output[] = $entry;
		}
	}

	return $output;
}

function table_row_flat($arr, $depth = 0)
{
	$output = array();

	foreach ($arr as $k => $v)
	{
		$v['depth'] = $depth;

		$output[isset($v['id']) ? $v['id'] : $k] = $v;

		if (isset($v['children']))
		{
			$children = table_row_flat($v['children'], $depth + 1);
			unset($v['children']);

			foreach ($children as $chk => $chv)
			{
				$output[$chk] = $chv;
			}
		}
	}

	return $output;
}

function table_register($table, $fields = array())
{
	$T = extend(array
	(
		'name' => 'row',
		'field_prefix' => '',
		'table_prefix' => 'table_',
		'refrence_table' => array(),
		'refrence_field' => '',
		'refrence_field_prefix' => '',
		'refrence_select_fields' => array(),
		'feature_tables' => array(),
		'fields' => array
		(
			'name' => '',
			'date' => '',
			'title' => '',
			'content' => '',
			'parent' => 0,
			'modified' => '',
			'mentions' => '',
			'hashtags' => '',
			'position' => 0
		),
		'types' => array
		(
			'name' => 'varchar(255)',
			'date' => 'varchar(64)',
			'title' => 'varchar(255)',
			'content' => 'text',
			'parent' => 'int(11)',
			'modified' => 'varchar(64)',
			'mentions' => 'varchar(255)',
			'hashtags' => 'varchar(255)',
			'position' => 'float',
			'tag_name' => 'char(32)',
			'tag_parent' => 'int(10)',
			'tag_count' => 'int(10)',
			'tag_date' => 'varchar(64)',
			'tag_modified' => 'varchar(64)',
			'pin_type' => 'char(32)',
			'pin_by' => 'int(10)',
			'pin_to' => 'int(10)',
			'pin_date' => 'varchar(64)',
			'term_id' => 'int(11)',
			'term_taxonomy' => 'char(32)',
			'term_name' => 'varchar(255)',
			'term_title' => 'varchar(255)',
			'term_parent' => 'int(11)',
			'term_count' => 'int(11)',
			'term_date' => 'varchar(64)',
			'term_modified' => 'varchar(64)',
			'context_id' => 'int(11)',
			'meta_key' => 'varchar(255)',
			'meta_value' => 'text'
		),
		'tables' => array(),
		'unique' => array('name'),
		'hash' => array(),
		'search' => array
		(
			'name' => 2,
			'mentions' => 4,
			'hashtags' => 10
		),
		'rules' => array
		(
			'*' => array('filter' => 'santize'),
			'name' => array('required' => true, 'filter' => 'slug'),
			'date' => array(),
			'title' => array('required' => true),
			'content' => array(),
			'parent' => array(),
			'modified' => array(),
			'mentions' => array(),
			'hashtags' => array(),
			'position' => array()
		),
		'term_fields' => array
		(
			'term_name' => '',
			'term_taxonomy' => '',
			'term_title' => '',
			'term_parent' => 0,
			'term_count' => 0,
			'term_date' => '',
			'term_modified' => ''
		),
		'term_rules' => array
		(
			'*' => array('filter' => 'santize'),
			'term_name' => array('required' => true, 'filter' => 'slug'),
			'term_taxonomy' => array(),
			'term_title' => array('required' => true),
			'term_parent' => array(),
			'term_count' => array(),
			'term_date' => array(),
			'term_modified' => array()
		),
		'args' => array
		(
			'http_args' => false,
			'id' => '',
			'url' => '',
			'name' => '',
			'page' => 1,
			'per_page' => 20,
			'order_by' => 'date',
			'order_type' => 'default',
			'group_by' => 'id',
			'seed' => '',
			'order' => 'DESC',
			'type' => '',
			'status' => 'public',
			'author' => '',
			'context' => '',
			'root' => '',
			'parent' => '',
			'relation' => 'and',
			'meta_query' => array(),
			'meta' => array(),
			'query' => '',
			'tag_name' => '',
			'tag_field' => 'tag_to',
			'tag_column' => '',
			'tag_self_column' => '',
			'tag_operator' => '=',
			'tag_join_field' => 'tag_to',
			'tag_join' => 'left',
			'year' => '',
			'month' => '',
			'hour' => '',
			'from_time' => '',
			'to_time' => '',
			'tree' => false,
			'flatten' => false,
			'max_depth' => 10,
			'admin' => false,
			'refrence_required' => false,
			'urlprefix' => '',
			'execute' => true
		),
		'db' => gonzo::instance('database'),
		'query' => array()
	), $table);

	foreach (array('fields', 'rules', 'unique', 'search', 'hash', 'feature_tables') as $prop)
	{
		if (isset($fields[$prop]))
		{
			$T[$prop] = $fields[$prop];
		}
	}

	foreach (array('types') as $prop)
	{
		if (isset($fields[$prop]))
		{
			$T[$prop] = extend($T[$prop], $fields[$prop]);
		}
	}

	$T['types'][$T['name'].'_id'] = 'int(11)';

	table_create($T, $T['feature_tables']);

	return $T;
}

function table_create($T, $tables = array())
{
	$feature_tables = array
	(
		'meta' => array('meta_key' => '', 'meta_value' => '', $T['name'].'_id' => -1),
		'tag' => array('tag_name' => '', 'tag_parent' => 0, 'tag_count' => 0, 'tag_date' => '', 'tag_modified' => ''),
		'pin' => array('pin_type' => '', 'pin_by' => '', 'pin_to' => '', 'pin_date' => ''),
		'taxonomyterms' => array($T['term_fields']),
		'taxonomyrelations' => array('term_id' => '', $T['name'].'_id' => ''),
		'taxonomymeta' => array('meta_key' => '', 'meta_value' => '', 'term_id' => '')
	);

	$T['db']->create_table($T['table_prefix'].$T['name'], $T['fields'], $T['types'], $T['unique']);

	foreach ($tables as $table)
	{
		if (is($feature_tables, $table))
		{
			$T['db']->create_table($T['table_prefix'].$T['name'].$table, $feature_tables[$table], $T['types']);
		}
	}
}

function table_clean()
{

}

function table_remove()
{

}

function table_row_unique_field($T, $row, $value, $field = 'name', $from = null)
{
	$select_row = 'select * from %s where (%s)';

	$key = $row[0];
	$cond = 0;

	if (is($row, 1) && is($row[1], 'id'))
	{
		$cond = $row[1]['id'];
	}

	$T['db']->query
	(
		$select_row,
		$T['table_prefix'].($from == null ? $T['name'] : $from),
		sql_fields(array("$key" => $value, 'id !=' => $cond), '=', 'and')
	);

	$entries = $T['db']->result();

	foreach ($entries as $entry)
	{
		if ($entry[$field] == $value)
		{
			return false;
		}
	}

	return true;
}

function table_row_unique_name($T, $row, $value)
{
	return table_row_unique_field($T, $row, $value, 'name', $T['name']);
}

function table_row_hashtags($T, $fields)
{
	$content = trim($fields['content']);

	if (empty($content))
	{
		$content = $fields['title'];
	}

	$content = ' '.$content;


	preg_match_all("/(\s+)#([A-Za-z0-9\/\.]*)/", $content, $hashtags);

	if (isset($hashtags[2]) && ! empty($hashtags[2]))
	{
		$hashtags = ltrim(implode(' #', array_unique($hashtags[2])), ' #');

		if ( ! empty($hashtags))
		{
			return '#'.$hashtags;
		}
	}

	return '';
}

function table_row_mentions($T, $fields)
{
	$content = trim($fields['content']);

	if (empty($content))
	{
		$content = $fields['title'];
	}

	$content = ' '.$content;

	preg_match_all("/(\s+)~([A-Za-z0-9\/\.]*)/", $content, $mentions);

	if (isset($mentions[2]) && ! empty($mentions[2]))
	{
		$mentions = ltrim(implode(' ~', array_unique($mentions[2])), ' ~');

		if ( ! empty($mentions))
		{
			return '~'.$mentions;
		}
	}

	return '';
}

function table_row_hashtags_remove($T, $fields)
{

}

function table_row_add($T, array $fields)
{
	$insert_row = 'insert into %s (%s) values (%s)';

	$date = time();

	$fields = extend($T['fields'], $fields);

	if (in($T['fields'], 'date') && empty($fields['date']))
	{
		$fields['date'] = $date;
	}

	if (in($T['fields'], 'modified') && empty($fields['modified']))
	{
		$fields['modified'] = (is($fields, 'date') ? $fields['date'] : $date);
	}

	if (in($T['fields'], 'title') && empty($fields['name']) && ! empty($fields['title']))
	{
		$fields['name'] = filter_slug($fields['title']);
	}

	if (in($fields, 'parent') && empty($fields['parent']))
	{
		unset($fields['parent']);
	}

	$errors = validate($fields, $T['rules'], 'table.'.$T['name']);

	if (count($errors) > 0)
	{
		return $errors;
	}

	if (in($T['fields'], 'hashtags'))
	{
		$fields['hashtags'] = table_row_hashtags($T, $fields);
	}

	if (in($T['fields'], 'mentions'))
	{
		$fields['mentions'] = table_row_mentions($T, $fields);
	}

	$insert_fields = array();

	foreach ($fields as $k => $v)
	{
		if (in($T['fields'], $k))
		{
			if (in($T['hash'], $k))
			{
				$insert_fields[$k] = filter_hash($v);
			} else
			{
				$insert_fields[$k] = $v;
			}
		}
	}

	$T['db']->query
	(
		$insert_row,
		$T['table_prefix'].$T['name'],
		sql_fields(array_keys($insert_fields), '`'),
		sql_fields(array_values($insert_fields))
	);

	$id = $T['db']->insert_id();

	if ($T['db']->error_number() == 1062)
	{
		$error = $T['db']->error_message();

		foreach ($T['unique'] as $field)
		{
			if (stristr($error, '\''.$field.'\'') !== false)
			{
				return valid_error($field, 'validator.duplicate_key', 'table.'.$T['name']);
			}
		}
	}

	return $id;
}

function table_row_update($T, array $fields, array $cond)
{
	$update_row = 'update %s set %s where (%s)';

	if (in($T['fields'], 'modified'))
	{
		$fields['modified'] = time();
	}

	if (is($fields, 'date'))
	{
		$date = $fields['date'];

		$day = is($fields, 'day') ? $fields['day'] : date('j', $date);
		$month = is($fields, 'month') ? $fields['month'] : date('n', $date);
		$year = is($fields, 'year') ? $fields['year'] : date('Y', $date);
		$hour = is($fields, 'hour') ? $fields['hour'] : date('H', $date);
		$minute = is($fields, 'minute') ? $fields['minute'] : date('i', $date);

		$fields['date'] = mktime($hour, $minute, 0, $month, $day, $year);

		$alias_fields = array('day', 'month', 'year', 'hour', 'minute');

		foreach ($alias_fields as $field_name)
		{
			if (isset($fields[$field_name]))
			{
				unset($fields[$field_name]);
			}
		}
	}

	$rules = $T['rules'];

	$update_fields = array_keys($fields);

	foreach ($rules as $field => $field_rules)
	{
		if ( ! in($fields, $field) && $field != '*')
		{
			unset($rules[$field]);
		}
	}

	$errors = validate($fields, $rules, 'table.'.$T['name']);

	if (count($errors) > 0)
	{
		return $errors;
	}

	if (in($T['fields'], 'hashtags'))
	{
		$fields['hashtags'] = table_row_hashtags($T, $fields);
	}

	if (in($T['fields'], 'mentions'))
	{
		$fields['mentions'] = table_row_mentions($T, $fields);
	}

	$update_fields = array();

	foreach ($fields as $k => $v)
	{
		if (in($T['fields'], $k))
		{
			if (in($T['hash'], $k))
			{
				$update_fields[$k] = filter_hash($v);
			} else
			{
				$update_fields[$k] = $v;
			}
		}
	}

	$T['db']->query
	(
		$update_row,
		$T['table_prefix'].$T['name'],
		sql_fields($update_fields, '=', ','),
		sql_fields($cond, '=', 'and')
	);

	return true;
}

function table_row_remove($T, array $cond)
{
	$select_row = 'select id from %s where (%s)';
	$delete_row = 'delete from %s where (%sid in (%s))';
	$update_row = 'update %s as terms left outer join %s as relations on (relations.'.$T['name'].'_id = %s) set (terms.term_count = terms.term_count - 1, terms.term_modified = %s) where (terms.id = relations.term_id)';

	$T['db']->query
	(
		$select_row,
		$T['table_prefix'].$T['name'],
		sql_fields($cond, '=', 'and')
	);

	$result = $T['db']->result();
	$ids = array_collect_key($result, 'id');

	if (empty($ids))
	{
		return;
	}

	if (in($T['feature_tables'], 'meta'))
	{
		$T['db']->query
		(
			$delete_row,
			$T['table_prefix'].$T['name'].'meta',
			$T['name'].'_',
			sql_fields($ids)
		);
	}

	if (in($T['feature_tables'], 'taxonomyterms'))
	{
		foreach ($ids as $id)
		{
			$T['db']->query
			(
				$update_row,
				$T['table_prefix'].$T['name'].'taxonomyterms',
				$T['table_prefix'].$T['name'].
				'taxonomyrelations',
				$id,
				time()
			);
		}
	}

	if (in($T['feature_tables'], 'taxonomyrelations'))
	{
		$T['db']->query
		(
			$delete_row,
			$T['table_prefix'].'taxonomyrelations',
			$T['name'].'_',
			sql_fields($ids)
		);
	}

	$T['db']->query
	(
		$delete_row,
		$T['table_prefix'].$T['name'],
		'', // no prefix of id
		sql_fields($ids) //sql_fields($where, '=', 'and')
	);
}

function table_row_get_ancestors($T, $id, $max_depth = 10, $context = '')
{
	$ancestors = array();
	$ancestor = table_row_get($T, array('id' => $id, 'tree' => false, 'max_depth' => 1), $context);
	$depth = 0;

	while (isset($ancestor[0]) && $ancestor[0]['parent'] != 0 && $depth < $max_depth)
	{
		$ancestors[] = $ancestor[0];
		$ancestor = table_row_get($T, array('id' => $ancestor[0]['parent'], 'tree' => false, 'max_depth' => 1), $context);
		$depth++;
	}

	return $ancestors;
}

function table_row_get_children($T, $id, $context = '')
{
	$children = table_row_get($T, array('parent' => $id, 'tree' => false, 'max_depth' => 1), $context);

	foreach ($children as $id => $entry)
	{
		$children[$id]['children'] = table_row_get_children($T, $entry['id'], $context);
	}

	return $children;
}

function table_query_init($T = array(), $args = array())
{
	return array
	(
		'timestamp' => time(),
		'stack' => array(),
		'row_count' => 0,
		'page_count' => 0,
		'rows' => array(),
		'rowscopy' => null,
		'row' => null,
		'depth' => 0,
		'args' => is($T, 'args') ? extend($T['args'], $args) : $args,
		'sql' => ''
	);
}

function table_query($T, array $args, $context = '')
{
	$Q = table_query_init($T, $args);
	$A = $Q['args'];
	$rows = array();

	if ($A['tree'] && $A['max_depth'] > 0 && not($A, 'parent'))
	{
		$A['parent'] = 0;
	}

	$A = gonzo::filtered($T['name'].( ! empty($context) ? '_'.$context : '').'_get_args', $A, $T);

	if ($A['execute'])
	{
		if ( ! empty($A['tag_column']))
		{
			if ( ! in($T['fields'], $A['tag_column']) && $A['tag_column'] != 'id')
			{
				unset($A[$A['tag_column']]);
				unset($A['tag']);
			}
		}

		if (not($A, 'tag_operator'))
		{
			$A['tag_operator'] = '=';
		}

		if (is($A, 'tag_self') && $A['tag_self'] === true && empty($A['tag_self_column']))
		{
			$A['tag_self_column'] = $A['tag_column'];
		}

		if (not($A, 'tag_join'))
		{
			$A['tag_join'] = 'left';
		}

		$fields = array('SQL_CALC_FOUND_ROWS '.$T['name'].'.*');

		if ( ! empty($T['refrence_table']) && ! empty($T['refrence_field']) && $T['refrence_field'] == $T['name'])
		{
			unset($A[$T['refrence_field']]);
		}

		if ( ! empty($T['refrence_table']) && ! empty($T['refrence_field']) && is($A, $T['refrence_field']))
		{

			$fields[] = $T['refrence_table']['name'].'.id as '.$T['refrence_table']['name'].'_id';

			foreach ($T['refrence_select_fields'] as $refrence_select)
			{
				$fields[] = $T['refrence_table']['name'].'.'.$refrence_select.' as '.$T['refrence_table']['name'].'_'.$refrence_select;
			}
		}

		if (is($A, 'meta'))
		{
			$fields[] = 'meta1.meta_key';
			$fields[] = 'meta1.meta_value';
		}

		if (is($A, 'order_by') && substr($A['order_by'], 0, 4) == 'meta')
		{
			$fields[] = 'meta_order.meta_key';
			$fields[] = 'meta_order.meta_value';
		}

		if ( ! empty($T['refrence_table']) && ! empty($T['refrence_field']) && is($A, $T['refrence_table']['name'].'meta'))
		{
			$fields[] = $T['refrence_field'].'meta.meta_key';
			$fields[] = $T['refrence_field'].'meta.meta_value';
		}

		if (is($A, 'tag'))
		{
			$fields[] = 'tag.tag_date';
		}

		if ( ! empty($A['search_query']))
		{
			$fields[] = table_row_get_search_query($T, $A['search_query']).' as '.$T['name'].'_search';

			$A['order_by'] = 'row_search';
		}

		$fields = gonzo::filtered($T['name'].( ! empty($context) ? '_'.$context : '').'_get_fields', $fields, $A);

		$select_row = sql_filter
		(
			'select %s from %s as '.$T['name'],
			sql_fields($fields, ''),
			$T['table_prefix'].$T['name']
		);

		if ( ! empty($T['refrence_table']) && ! empty($T['refrence_field']) && is($A, $T['refrence_field']))
		{
			$select_row .= sql_filter
			(
				' %s join %s as '.$T['refrence_table']['name'].' on ('.$T['name'].'.%s = '.$T['refrence_table']['name'].'.id)',
				! $A['refrence_required'] ? 'left' : '',
				$T['table_prefix'].$T['refrence_table']['name'],
				$T['refrence_field']
			);
		}

		if ( ! empty($A['tag']))
		{
			if ( ! empty($A['tag_column']))
			{
				unset($A[$A['tag_column']]);

				$select_row .= sql_filter
				(
					' %s join %s as tag on (tag.tag_name = %s and tag.%s %s '.$T['name'].'.%s',
					$A['tag_join'],
					$T['table_prefix'].'tag',
					$A['tag_name'],
					$A['tag_join_field'],
					$A['tag_operator'],
					$A['tag_column']
				);

				if ($A['tag_self'])
				{
					$select_row .= sql_filter
					(
						' or '.$T['name'].'.%s %s %s',
						$A['tag_self_column'],
						$A['tag_operator'],
						$A['tag']
					);
				}

				$select_row .= ')';
			}
		}

		if (is($A, 'meta'))
		{
			$select_row .= sql_filter
			(
				' left join %s as meta1 on ('.$T['name'].'.id = meta1.'.$T['name'].'_id && (meta1.meta_key = %s))',
				$T['table_prefix'].$T['name'].'meta',
				sql_fields($A['meta'], '\'', 'or meta1.meta_key = ')
			);
		}

		if (is($A, 'order_by') && substr($A['order_by'], 0, 4) == 'meta')
		{
			$meta_order = substr($A['order_by'], 5);

			$select_row .= sql_filter
			(
				' inner join %s as meta_order on ('.$T['name'].'.id = meta_order.'.$T['name'].'_id and meta_order.meta_key = %s)',
				$T['table_prefix'].$meta,
				$meta_order
			);
		}

		if ( ! empty($T['refrence_table']) && ! empty($T['refrence_field']) && is($A, $T['refrence_table']['name'].'meta'))
		{
			$select_row .= sql_filter
			(
				' left join %s as '.$T['refrence_table']['name'].'meta on ('.$T['refrence_table']['name'].'.id = '.$T['refrence_table']['name'].'meta.'.$T['name'].'_id && ('.$T['refrence_table']['name'].'meta.meta_key = %s))',
				$T['refrence_table']['table_prefix'].$T['refrence_table']['name'].$T['name'].'meta',
				sql_fields($A[$T['refrence_table']['name'].'meta'], '\'', 'or '.$T['refrence_table']['name'].'meta.meta_key = ')
			);
		}

		if (is($A, 'meta_query'))
		{
			$select_row .= sql_filter
			(
				' inner join %s as meta2 on ('.$T['name'].'.id = meta2.'.$T['name'].'_id)',
				$T['table_prefix'].$T['name'].'meta'
			);
		}

		if (is($A, 'tax_query'))
		{
			$select_row .= sql_filter
			(
				' join %s as relation on '.$T['name'].'.id = relation.'.$T['name'].'_id',
				$T['table_prefix'].'taxonomyrelations'
			);

			$select_row .= sql_filter
			(
				' left join %s as term on relation.term_id = term.id',
				$T['table_prefix'].'taxonomyterms'
			);
		}

		$where = array();
		$field_aliases = array('id', 'year', 'month', 'hour', 'from_time', 'to_time');

		foreach ($A as $key => $value)
		{
			if (empty($value) || $value == 'any')
			{
				continue;
			}

			if ($key == 'date' || in($T['fields'], $key) || in($field_aliases, $key))
			{
				if (in($T['hash'], $key))
				{
					$value = filter_hash($value);
				}

				$date_alias = ($key == 'from_time' || $key == 'to_time');
				$operator = '';

				switch ($key)
				{
					case 'from_time':
						$operator = '>=';
					break;
					case 'to_time':
						$operator = '<=';
					break;
					default:
					break;
				}

				switch ($key)
				{
					case 'year':
					case 'month':
					case 'hour':
						$key = $T['name'].'.'.strtoupper($key).'(FROM_UNIXTIME('.$T['name'].'.date))';
					break;
					case $T['refrence_field']:
						$key = $T['name'].'.'.$T['refrence_field'];
					break;
					default:
						$key = $T['name'].'.'.($date_alias ? 'date' : $key);
					break;
				}

				$where += [ $key => $value ];
			}
		}

		$where_before = '';
		$where_before = gonzo::filtered($T['name'].( ! empty($context) ? '_'.$context : '').'_query_where_sql_before', $where_before, $A);

		$where_after = '';
		$where_after = gonzo::filtered($T['name'].( ! empty($context) ? '_'.$context : '').'_query_where_sql_after', $where_after, $A);

		$select_row .= sql_filter
		(
			' where (%s%s%s)',
			$where_before,
			! empty($where) ? sql_fields($where, '=', 'and') : '1 = 1',
			$where_after
		);

		if ( ! empty($A['tag']))
		{
			$select_row .= sql_filter
			(
				' and ((tag.tag_name = %s && tag.%s = %s'.$A['tag'].')',
				sql_fields($A['tag_name']),
				sql_fields($A['tag_field'], ''),
				sql_fields($A['tag'])
			);

			if ( ! empty($A['tag_column']) && $A['tag_self'])
			{
				$select_row .= sql_filter
				(
					' or '.$T['name'].'.%s %s %s',
					sql_fields($A['tag_self_column'], ''),
					$A['tag_operator'],
					$A['tag']
				);
			}

			$select_row .= ')';
		}

		if (is($A, 'meta_query'))
		{
			$relation = 'and';

			if (is($A['meta_query'], 'relation'))
			{
				$relation = $A['meta_query']['relation'];
				unset($A['meta_query']['relation']);
			}

			$meta = array_prefix_key($A['meta_query'], 'meta_');

			$select_row .= sql_filter
			(
				' and (%s)',
				sql_fields($A['meta_query'], '=', $relation, 'meta2.')
			);
		}

		if (is($A, 'tax_query'))
		{
			$first = true;
			$select_row .= ' and (';

			$relation = 'or';

			if (is($A['tax_query'], 'relation'))
			{
				$relation = $A['tax_query']['relation'];
				unset($A['tax_query']['relation']);
			}

			foreach ($A['tax_queries'] as $tax_queries)
			{
				$tax_first = true;
				$tax_relation = $relation;
				$tax_operator = 'in';

				if (is($tax_queries, 'relation'))
				{
					$tax_relation = $tax_queries['relation'];
					unset($tax_queries['relation']);
				}

				if (is($tax_queries, 'operator'))
				{
					$tax_operator = $tax_queries['operator'];
					unset($tax_queries['operator']);
				}

				$select_row .= ($first ? '' : ' '.$relation.' ').'(';

				foreach ($tax_queries as $tax_query)
				{
					$tax = array_prefix_key($tax_query, 'term.term_', array('field', 'terms'));

					if (not($tax_query, 'field'))
					{
						$tax_query['field'] = 'name';
					}

					$field = $tax_query['field'];
					$terms = $tax_query['terms'];

					usnet($tax_query['field']);
					usnet($tax_query['terms']);

					if ($field != 'id')
					{
						$field = 'term.term_'.$field;
					}

					if ( ! is_array($terms))
					{
						$terms = explode(',', $terms);
					}

					if ($tax_operator == 'not in')
					{
						$not_tax = $tax;
						$not_tax += [ "$field in" => $terms ];

						$terms = '&'.filter_sql
						(
							'(select '.$T['name'].'_id from %s as relation left join %s as term on (relation.term_id = term.id) where (%s))',
							$T['table_prefix'].'taxonomyrelations',
							$T['table_prefix'].'taxonomyterms',
							sql_fields($not_tax, '=', 'and')
						);

						$field = $T['name'].'.id';
					} else if ($tax_operator == 'between')
					{
						$terms = '&'.intval($terms[0]).' and '.intval($terms[1]);
					}

					$field .= ' '.$tax_operator;

					$tax += [ "$field" => $terms ];

					$select_row .= sql_filter
					(
						'%s(%s)',
						($tax_first ? '' : ' '.$tax_relation.' '),
						sql_fields($tax, '=', 'and')
					);

					$tax_first = false;
				}
			}

			$select_row .= ')';
		}

		if ( ! empty($A['group_by']))
		{
			$group_by = $A['group_by'];

			if ($group_by == 'id' || in($T['fields'], $group_by))
			{
				$group_by = $T['name'].'.'.$group_by;
			}

			$select_row .= sql_filter(' group by %s', $group_by);
		}

		if ( ! empty($A['search_query']))
		{
			$select_row .= sql_filter(' having %', $T['name'].'_search > 0');
		}

		$A['page'] = gonzo::filtered($T['name'].'_get_page', is($A, 'page') ? $A['page'] : 1, $A);
		$A['per_page'] = gonzo::filtered($T['name'].'_get_per_page', is($A, 'per_page') ? $A['per_page'] : 50, $A);
		$A['order'] = gonzo::filtered($T['name'].'_get_order', is($A, 'order') ? $A['order'] : 'desc', $A);
		$A['order_by'] = $T['name'].'.'.gonzo::filtered($T['name'].'_get_order_by', $A['order_by'], $A);

		if (substr($A['order_by'], 0, 4) == 'meta')
		{
			$A['order_by'] = 'meta_order.meta_value';
		} else if ($A['order_by'] == 'random')
		{
			$A['order_by'] = 'RAND('.$A['seed'].')';
		}

		if (is($A, 'search_query'))
		{
			$A['order_by'] = $T['name'].'_search';
		}

		if (is($A, 'order_by'))
		{
			$select_row .= sql_filter
			(
				' order by %s %s',
				$A['order_by'].($A['order_type'] == 'numeric' ? '+0' : ''),
				$A['order']
			);
		}

		if (is($A, 'per_page') && $A['per_page'] > 0)
		{
			$select_row .= sql_filter
			(
				' limit %s, %s',
				($A['page'] - 1) * $A['per_page'],
				$A['per_page']
			);
		}

		$Q['sql'] = $select_row;

		$T['db']->query($Q['sql']);

		$Q['rows'] = $T['db']->result();
		$Q['page_count'] = 0;
		$Q['row_count'] = $T['db']->num_rows();

		if ($Q['row_count'] && ( ! $A['tree'] || $A['parent'] == 0))
		{
			$Q['page_count'] = intval(ceil($Q['row_count'] / $A['per_page']));
		} else
		{
			$Q['row_count'] = 0;
			$Q['page_count'] = 0;
		}

		if ($A['tree'] === true && $A['max_depth'] > 0)
		{
			$parent = $A['parent'];

			$A['max_depth']--;

			foreach ($Q['rows'] as $id => $entry)
			{
				$Q['rows'][$id]['children'] = array();

				$children_args = $A;
				$children_args['parent'] = $entry['id'];

				$children = table_row_get($T, $children_args);

				if ( ! empty($children))
				{
					$Q['rows'][$id]['children'] = $children;
				}
			}

			if ($parent === 0 && $A['flatten'])
			{
				$Q['rows'] = table_row_flat($Q['rows']);
			}
		}

		if (isset($A['pagination']) && $A['pagination'] === false)
		{
			$Q['page_count'] = 0;
		}
	}

	$Q['args'] = $A;

	return gonzo::filtered($T['name'].( ! empty($context) ? '_'.$context : '').'_query', $Q, $A);
}

function table_row_get($T, array $args, $context = '')
{
	$query = table_query($T, $args, $context);

	return $query['rows'];
}

function table_row_add_term($T, $row_id, $term_id, $taxonomy)
{
	$update_term = 'update %s set (term_count = term_count + 1, term_modified = %s) where (id = %s)';
	$insert_relation = 'insert into %s (term_id, '.$T['name'].'_id) values (%s, %s)';

	if ($row_id <= 0)
	{
		return false;
	}

	if (table_taxonomy_has_term($T, array('id' => $term_id), $taxonomy) && table_row_has_term($T, $row_id, $term_id, $taxonomy) === false)
	{
		$T['db']->query
		(
			$update_term,
			$T['table_prefix'].'taxonomyterms',
			sql_fields(time()),
			sql_fields($term_id)
		);

		$T['db']->query
		(
			$insert_relation,
			$T['table_prefix'].'taxonomyrelations',
			sql_fields($term_id),
			sql_fields($row_id)
		);

		return $T['db']->insert_id();
	}

	return false;
}

function table_row_set_terms($T, $row_id, $term_ids, $taxonomy)
{
	$update_term = 'update %s as terms left outer join %s as relations on (relations.'.$T['name'].'_id = %s) set (term_count = term_count + 1, term_modified = %s) where (terms.term_taxonomy = %s and terms.id = relations.term_id)';
	$delete_relation = 'delete relations.* from taxonomyrelations as relations left join taxonomyterms as terms on (terms.id = relations.term_id and terms.term_taxonomy = %s) where ('.$T['name'].'_id = %s and terms.term_taxonomy = %s)';
	$update_terms = 'update %s as terms set (term_count = term_count + 1, term_modified = %s) where (id in (%s) and terms.term_taxonomy = %s)';
	$insert_relations = 'insert into %s (term_id, '.$T['name'].'_id) values %s';

	if ($row_id <= 0)
	{
		return false;
	}

	if ( ! is_array($term_ids))
	{
		$term_ids = array($term_ids);
	}

	$count = count($term_ids);

	$T['db']->query
	(
		$update_term,
		$T['table_prefix'].'taxonomyterms',
		$T['table_prefix'].'taxonomyrelations',
		sql_fields($row_id),
		sql_fields(time()),
		sql_fields($taxonomy)
	);

	$T['db']->query
	(
		$delete_relation,
		$T['table_prefix'].'taxonomyrelations',
		$T['table_prefix'].'taxonomyterms',
		sql_fields($taxonomy),
		sql_fields($row_id),
		sql_fields($taxonomy)
	);

	if ($count > 0)
	{
		$T['db']->query
		(
			$update_terms,
			$T['table_prefix'].'taxonomyterms'.
			time(),
			sql_fields($term_ids),
			$taxonomy
		);

		$relations = array();

		for ($i = 0; $i < $count; $i++)
		{
			$relations[] = sql_fields(array($term_ids[$i], $row_id));
		}

		$T['db']->query
		(
			$insert_relations,
			$T['table_prefix'].'taxonomyrelations',
			'('.implode('),(', $fields).')'
		);
	}
}

function table_row_has_term($T, $row_id, $term_id, $taxonomy)
{
	$select_relations = 'select id from %s where (term_id = %s and '.$T['name'].'_id = %s)';

	if ($row_id <= 0)
	{
		return false;
	}

	$T['db']->query
	(
		$select_relations,
		$T['table_prefix'].'taxonomyrelations',
		sql_fields($term_id),
		sql_fields($row_id)
	);

	$result = $T['db']->result();

	return count($result) > 0;
}

function table_row_remove_term($T, $row_id, $term_id, $taxonomy)
{
	$update_terms = 'update %s as terms set (term_count = term_count - 1, term_modified = %s) where (id in (%s) and terms.term_taxonomy = %s)';
	$delete_terms = 'delete from %s where (term_id in (%s) and '.$T['name'].'_id = %s)';

	if ($row_id <= 0)
	{
		return;
	}

	if (table_row_has_term($T, $row_id, $term_id, $taxonomy) !== false)
	{
		$T['db']->query
		(
			$update_terms,
			$T['table_prefix'].'taxonomyterms',
			time(),
			sql_fields(array($term_id)),
			sql_fields($taxonomy)
		);

		$T['db']->query
		(
			$delete_terms,
			$this->prefx.'taxonomyrelations',
			sql_fields(array($term_id)),
			sql_fields($row_id)
		);
	}
}

function table_row_get_terms($T, $row_id, $taxonomy, $meta_keys = array())
{
	$select_terms = 'select %s from %s as terms left join %s as relations on (relations.term_id = terms.id and terms.term_taxonomy = %s) where (relations.'.$T['name'].'_id = %s) order by id asc';

	if ($row_id <= 0)
	{
		return array();
	}

	$fields = array('terms.id', 'terms.term_taxonomy', 'terms.term_name', 'terms.term_title', 'terms.term_parent', 'terms.term_count', 'terms.term_date', 'terms.term_modified', 'relations.'.$T['name'].'_id');

	$T['db']->query
	(
		$select_terms,
		sql_fields($fields),
		$T['table_prefix'].'taxonomyterms',
		$T['table_prefix'].'taxonomyrelations',
		sql_fields($taxonomy),
		sql_fields($row_id)
	);

	$result = $T['db']->result();

	if (is_array($meta_keys) && ! empty($meta_keys))
	{
		$result = table_row_group($result, 'id', array('meta_key', 'meta_value'));
	}

	return $result;
}

function table_term_unique_name($T, $row, $value)
{
	return table_row_unique_field($T, $row, $value, 'term_name', 'taxonomyterms');
}

function table_taxonomy_add_term($T, $taxonomy, $fields)
{
	$select_terms = 'select id from %s where (term_name = %s and term_taxonomy = %s';
	$insert_term = 'insert into %s (%s) values (%s)';
	$time = time();

	$fields = extend($T['term_fields'], $fields);

	$fields['term_taxonomy'] = $taxonomy;
	$fields['term_date'] = $fields['term_modified'] = $time;

	if (not($fields, 'term_name') && is($fields['term_title']))
	{
		$fields['term_name'] = $fields['term_title'];
	}

	$errors = validate($fields, $T['term_rules'], 'table.'.$T['name']);

	if (count($errors) > 0)
	{
		return $errors;
	}

	$T['db']->query
	(
		$select_terms,
		$T['table_prefix'].'taxonomyterms',
		sql_fields($fields['term_name']),
		sql_fields($fields['term_taxonomy'])
	);

	$result = $T['db']->result();

	if (count($result) > 0)
	{
		return $result[0]['id'];
	}

	$T['db']->query
	(
		$insert_term,
		$T['table_prefix'].'taxonomyterms',
		sql_fields(array_keys($fields), ''),
		sql_fields(array_values($fields))
	);

	return $T['db']->insert_id();
}

function table_taxonomy_update_terms($T, $taxonomy, $fields, $cond, $rules = array())
{
	$update_terms = 'update %s set (%s) where (%s)';

	$cond['term_taxonomy'] = $taxonomy;

	$fields['term_modified'] = time();

	$errors = validate($fields, $T['term_rules'], 'table.'.$T['name']);

	if (count($errors) > 0)
	{
		return $errors;
	}

	$T['db']->query
	(
		$update_terms,
		$T['table_prefix'].'taxonomyterms',
		sql_fields($fields),
		sql_fields($cond, '=', 'and')

	);

	return true;
}

function table_taxonomy_has_term($T, $taxonomy, $cond)
{
	$select_term = 'select id from %s where (%s)';

	$cond['term_taxonomy'] = $taxonomy;

	$T['db']->query
	(
		$select_term,
		$T['table_prefix'].'taxonomyterms',
		sql_fields($cond, '=', 'and')
	);

	$result = $T['db']->result();

	return count($result) > 0;
}

function table_taxonomy_get_terms($T, $taxonomy, $cond)
{
	$select_terms = 'select %s from %s where (%s) order by id asc';
	$cond['term_taxonomy'] = $taxonomy;

	$fields = array_keys($T['term_fields']);

	$T['db']->query
	(
		$select_terms,
		sql_fields($fields, ''),
		$T['table_prefix'].'taxonomyterms',
		sql_fields($cond, '=', 'and')
	);

	return $T['db']->result();
}

function table_taxonomy_remove_terms($T, $taxonomy, $cond)
{
	$select_terms = 'select id from %s where (%s)';
	$delete_relations = 'delete from %s where (term_id in (%s))';
	$delete_meta = 'delete from %s where (term_id in (%s))';
	$delete_terms = 'delete from %s where (%s)';

	$cond['term_taxonomy'] = $taxonomy;

	$T['db']->query
	(
		$select_terms,
		$T['table_prefix'].'taxonomyterms',
		sql_fields($cond, '=', 'and')
	);

	$terms = $T['db']->result();
	$ids = array_key_values($terms, 'id');

	$T['db']->query
	(
		$delete_relations,
		$T['table_prefix'].$T['name'].'taxonomyrelations',
		sql_fields($ids)
	);

	$T['db']->query
	(
		$delete_meta,
		$T['table_prefix'].$T['name'].'taxonomymeta',
		sql_fields($ids)
	);

	$T['db']->query
	(
		$delete_terms,
		$T['table_prefix'].$T['name'].'taxonomyterms',
		sql_fields($cond)
	);
}

function table_taxonomy_add_relation()
{

}

function table_taxonomy_has_relation()
{

}

function table_taxonomy_remove_relation($T, $taxonomy, $cond = array())
{
	$update_terms = 'update %s set (term_count = 0) where (%s)';
	$delete_relations = 'delete from %s where (%s)';

	$cond['term_taxonomy'] = $taxonomy;

	$T['db']->query
	(
		$update_terms,
		$T['table_prefix'].$T['name'].'taxonomyterms',
		sql_fields($cond)
	);

	$T['db']->query
	(
		$delete_relations,
		$T['table_prefix'].$T['name'].'taxonomyrelations',
		sql_fields($cond)
	);
}

function table_get_meta($T, $cond = array(), $single = true, $context = '')
{
	$select_meta = 'select id, meta_key, meta_value, %s from %s where (%s)';

	if (empty($cond))
	{
		return false;
	}

	if (empty($context))
	{
		$context = $T['name'];
	}

	$T['db']->query
	(
		$select_meta,
		$context == 'taxonomy' ? 'term_id' : 'id',
		$T['table_prefix'].$context.'meta',
		sql_fields($cond, 'in', 'and')
	);

	return $T['db']->result();
}

function table_row_get_meta($T, $row_id, $meta_key, $single = true)
{
	$cond = array('id' => $row_id, 'meta_key' => $meta_key);

	return table_get_meta($T, $cond, $single, '');
}

function table_taxonomy_get_meta($T, $taxonomy, $term_id, $meta_key, $single = true)
{
	$cond = array('term_taxonomy' => $taxonomy, 'term_id' => $term_id, 'meta_key' => $meta_key);

	return table_get_meta($T, $cond, $single, 'taxonomy');
}

function table_add_meta($T, $fields, $single = true, $context = '')
{
	$update_meta = 'update %s set %s where (id = %s)';
	$insert_meta = 'insert into %s (%s) values (%s)';

	if (empty($fields))
	{
		return false;
	}

	if (empty($context))
	{
		$context = $T['name'];
	}

	if ($single)
	{
		$cond = $fields;
		unset($cond['meta_value']);

		$result = table_get_meta($T, $cond, $single, $context);

		if (count($result) > 0)
		{
			$T['db']->query
			(
				$update_meta,
				$T['table_prefix'].$context.'meta',
				sql_fields($fields, '=', ','),
				sql_fields($result[0]['id'])
			);

			return $result[0]['id'];
		}
	}

	$T['db']->query
	(
		$insert_meta,
		$T['table_prefix'].$context.'meta',
		sql_fields(array_keys($fields), ''),
		sql_fields(array_values($fields))
	);

	return $T['db']->insert_id();
}

function table_update_meta($T, $fields, $cond = array(), $context = '')
{
	$update_meta = 'update %s set (%s) where (%s)';

	if (empty($fields) || empty($cond))
	{
		return false;
	}

	if (empty($context))
	{
		$context = $T['name'];
	}

	$T['db']->query
	(
		$update_meta,
		$T['table_prefix'].$context.'meta',
		sql_fields($fields, '=', ','),
		sql_fields($cond, 'in', 'and')
	);

	if ($T['db']->affected_rows() == 0)
	{
		return table_add_meta($T, $fields, true, $context);
	}

	return true;
}

function table_remove_meta($T, $cond = array(), $context = '')
{
	$delete_meta = 'delete from %s where (%s)';

	if (empty($cond))
	{
		return false;
	}

	if (empty($context))
	{
		$context = $T['name'];
	}

	$T['db']->query(sql_filter
	(
		$delete_meta,
		$T['table_prefix'].$context.'meta',
		sql_fields($cond, 'in', 'and')
	));
}

function table_row_get_search_query($T, $query)
{
	$delimiters = array(' ', ',', '.', '-', '_', '&');
	$d = array_pop($delimiters);

	foreach($delimiters as $delimiter)
	{
		$query = str_replace($delimiter, $d, $query);
	}

	$query = explode($d, $query);

	$sum = '';
	$sum_title = '';
	$sum_artist = '';
	$sum_hashtags = '';

	for ($i = 0; $i < count($query); $i++)
	{
		$query[$i] = filter_slug(trim($query[$i]));

		if ( ! empty($query[$i]))
		{
			$query[$i] = strtolower($query[$i]);

			if (strlen($query[$i]) > 6)
			{
				$query[$i] = substr($query[$i], 0, 6);
			}

			if (strlen($query[$i]) < 3)
			{
				continue;
			}

			if ($i > 0)
			{
				$sum .= ' + ';
			}

			$first = true;

			foreach ($this->search as $search_field => $search_order)
			{
				if ( ! $first)
				{
					$sum .= ' + ';
				}

				$sum .= '((LENGTH('.$T['name'].'.'.$search_field.') - LENGTH(REPLACE(LOWER('.$T['name'].'.'.$search_field.'), \''.$query[$i].'\', \'\'))) / '.$search_order.')';

				$first = false;
			}
		}

		if ($i > 10)
		{
			break;
		}
	}

	if (empty($sum))
	{
		return '0';
	}

	return 'SUM('.$sum.') * ('.$T['name'].'.order + 1)';
}
