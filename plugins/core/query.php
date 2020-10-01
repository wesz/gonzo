<?php

gonzo::import('gonzo.table');

function query_rows(&$T, $args, $context = '')
{
	$T['query'] = table_row_query($T, $args, $context);
	$T['query']['rowscopy'] = $T['query']['rows'];

	gonzo::trigger($T['name'].( ! empty($context) ? '_'.$context : '').'_query_rows', $T, $args);
}

function query_have_rows($T)
{
	return ! empty($T['query']['rows']);
}

function query_children(&$T)
{
	if (query_have_children($T))
	{
		$T['query']['rows'] = $T['query']['row']['children'];
		$T['query']['rowscopy'] = $T['query']['rows'];
	}
}

function query_have_children($T)
{
	if ( ! empty($T['query']['row']))
	{
		if (is($T['query']['row'], 'children') && is_array($T['query']['row']['children']))
		{
			return true;
		}
	}

	return false;
}

function query_add_row(&$T, $row)
{
	if ( ! empty($row))
	{
		$T['query']['rows'][] = $row;
	}
}

function query_set_rows(&$T, $rows)
{
	$T['query']['rows'] = $rows;
}

function query_get_rows($T, $from_copy = false)
{
	if ($from_copy)
	{
		return $T['query']['rowscopy'];
	}

	return $T['query']['rows'];
}

function query_set_row(&$T, $row)
{
	if ( ! empty($row))
	{
		$T['query']['row'] = $row;
	}
}

function query_get_row($T)
{
	return $T['query']['row'];
}

function query_row(&$T, $row = NULL)
{
	if ($row !== NULL && ! empty($row))
	{
		$T['query']['row'] = $row;

		return true;
	}

	if ( ! empty($T['query']['rows']))
	{
		$T['query']['row'] = array_shift($T['query']['rows']);

		return true;
	}

	return false;
}

function query_rewind(&$T)
{
	$T['query']['rows'] = $T['query']['rowscopy'];
}

function query_reset(&$T)
{
	$T['query']['rows'] = array();
}

function query_get($T, $field)
{
	if (is($T['query']['row'], $field))
	{
		return gonzo::filtered($T['name'].'_'.$field.'_field', $T['query']['row'][$field]);
	}

	return '';
}

function query_has($T, $field)
{
	if (is($T['query']['row'], $field))
	{
		return true;
	}

	return false;
}

function query_get_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$url = '/'.$T['name'];

	if (is($A, 'type'))
	{
		$url .= '/'.$A['type'];
	}

	$url .= '/search';

	return gonzo::url($url);
}


function query_get_search_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$url = '/'.$T['name'];

	if (is($A, 'type'))
	{
		$url .= '/'.$A['type'];
	}

	$url .= '/search';

	return gonzo::url($url);
}

function query_get_search_query($T)
{
	$q = gonzo::input('get', 'q');

	if ($q)
	{
		return filter_santize(urldecode($q));
	}

	return '';
}

function query_get_add_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$url = '/'.$T['name'];

	if (is($A, 'type'))
	{
		$url .= '/'.$A['type'];
	}

	$url .= '/add';

	return gonzo::url($url);
}

function query_get_edit_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$url = '/'.$T['name'];

	if (is($A, 'type'))
	{
		$url .= '/'.$A['type'];
	}

	$url .= '/edit';

	if (isset($Q['row']['id']))
	{
		$url .= '/'.$Q['row'];
	}
}

function query_get_meta($T, $meta_key)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if (isset($Q['row'][$T['name'].'meta']) AND isset($Q['row'][$T['name'].'meta'][$meta_key]))
	{
		return $Q['row'][$T['name'].'meta'][$meta_key];
	}

	return table_get_meta($T, $Q['row']['id'], $meta_key, $single);
}

function query_has_pagination($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	return $Q['page_count'] > 1;
}

function query_get_pagination($T, $full = TRUE)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$pagination = array();

	$prev = query_get_prev_link($T);
	$next = query_get_next_link($T);

	if ( ! empty($prev) OR ! empty($next))
	{
		if ( ! empty($prev))
		{
			$pagination[] = $prev;
		}

		if ($Q['page_count'] > 1)
		{
			if ($A['page'] >= 1)
			{
				$links[] = $A['page'];
			}

			if ($page >= 3 )
			{
				$links[] = $A['page'] - 1;
				$links[] = $A['page'] - 2;
			}

			if (($A['page'] + 2 ) <= $Q['page_count'])
			{
				$links[] = $A['page'] + 2;
				$links[] = $A['page'] + 1;
			}

			if ( ! in_array(1, $links))
			{
				$pagination = query_get_page_link($T, 1);

				if ( ! in_array(2, $links))
				{
					$pagination[] = '...';
				}
			}

			sort($links);

			foreach ($links as $link)
			{
				$pagination[] = query_get_page_link($T, $link);
			}

			if ( ! in_array($Q['page_count'], $links))
			{
				if ( ! in_array($Q['page_count'] - 1, $links))
				{
					$pagination[] = '...';
				}

				$pagination[] = query_get_page_link($T, $Q['page_count']);
			}
		}

		if ( ! empty($next))
		{
			$pagination[] = $next;
		}
	}

	return gonzo::filtered($T['name'].'_pagination', $pagination, $A['page'], $Q['page_count']);
}

function query_get_page_link($T, $page, $link_name = 'page', $page_name = 'page')
{
	$Q = $T['query'];
	$A = $Q['args'];

	if ($page <= 0 || $page > $Q['page_count'])
	{
		return '';
	}

	$url = '/'.$T['name'].'/index';

	if ( ! empty($page_name))
	{
		$url .= '/'.$page_name.'/';
	}

	$url .= $page;

	return gonzo::filtered($T['name'].'_'.$link_name.'_link', gonzo::url($url), $page, $Q['page_count']);
}

function query_get_next_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if ($A['page'] + 1 > $Q['page_count'])
	{
		return '';
	}

	return query_get_page_link($T, $A['page'] + 1, 'next');
}

function query_get_prev_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if ($A['page'] - 1 <= 0)
	{
		return '';
	}

	return $this->get_page_link($A['page'] - 1, 'next');
}

function query_push(&$T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$T['query']['stack'][] = query_copy($T);

	return true;
}

function query_pop(&$T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if ( ! empty($Q['stack']))
	{
		query_replace($T, array_pop($Q['stack']));

		return true;
	}

	return false;
}

function query_copy($T)
{
	$Q = $T['query'];
	unset($Q['stack']);

	return $T['query'];
}

function query_replace(&$T, $query)
{
	unset($query['stack']);

	$T['query'] = extend($T['query'], $query);
}

function query_is_main($T)
{
	return count($T['query']['stack']) == 0;
}

function query_get_args($T)
{
	return $T['query']['args'];
}

function query_get_arg($T, $name)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if (is($A, $name))
	{
		return $A[$name];
	}

	return NULL;
}

function query_set_arg(&$T, $name, $value)
{
	$T['query']['args'][$name] = $value;
}

function query_get_page($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	return $A['page'];
}

function query_get_page_count($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	return $Q['page_count'];
}

function query_get_row_count($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	return $Q['row_count'];
}

function query_get_sql($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	return $Q['sql'];
}

function query_refrence_get($T, $field)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if (is($Q['row'], $T['refrence_table']['name'].'_'.$field))
	{
		return $Q['row'][$T['refrence_table']['name'].'_'.$field];
	}

	return '';
}

function query_refrence_get_link($T)
{
	$Q = $T['query'];
	$A = $Q['args'];

	$url = '/'.$T['refrence_field'];
	$url .= '/'.query_refrence_get($T, 'name');

	return gonzo::url($url);
}

function query_refrence_get_meta($T, $meta_key, $single = true)
{
	$Q = $T['query'];
	$A = $Q['args'];

	if (isset($Q['row'][$T['refrence_field']]) && $Q['row'][$T['refrence_field']] != -1)
	{
		return table_row_get_meta($T['refrence_table'], $Q['row'][$T['refrence_field']], $meta_key, $single);
	}

	return '';
}
