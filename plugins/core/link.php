<?php

gonzo::import('html-api');

global $links;

$links = array();

function link_sort($a, $b)
{
	return ($a['order'] < $b['order'] ? -1 : 1);
}

function link_add($name, $id, $label, $link, $order = 5)
{
	global $links;

	if ( ! isset($links[$name]))
	{
		$links[$name] = array();
	}

	$links[$name][$id] = array
	(
		'id' => $id,
		'label' => $label,
		'link' => $link,
		'order' => $order,
		'children' => array()
	);
}

function link_add_to($name, $parent_id, $id, $label, $link, $order = 5)
{
	global $links;

	if (isset($links[$name]))
	{
		$links[$name][$parent_id]['children'][$id] = array
		(
			'id' => $id,
			'label' => $label,
			'link' => $link,
			'order' => $order,
			'children' => array()
		);
	}
}

function link_remove($name, $id)
{
	global $links;

	if (isset($links[$name]) && isset($links[$name][$id]))
	{
		unset($links[$name][$id]);
	}
}

function link_clear($name)
{
	global $links;

	if (isset($links[$name]))
	{
		$links[$name] = array();
	}
}

function link_html($link, $wrap = true, $root = true)
{
	uasort($link, 'link_sort');

	$html = '';

	foreach ($link as $link_id => $link_item)
	{
		if (empty($link_item['link']))
		{
			$html .= $link_item['label'];
		} else
		{
			$html .= a($link_item['label'], $link_item['link'], ($wrap ? '' : 'link link-'.$link_id));
		}

		if (isset($html['children']))
		{
			$html .= link_html($link_item[$link_id]['children'], $wrap, false);
		}
	}

	return $html;
}

function link_render($name, $wrap = true)
{
	global $links;

	if (isset($links[$name]))
	{
		gonzo::trigger('link.'.$name.'.render');

		$links[$name] = gonzo::filtered('link.'.$name, $links[$name], $name, $wrap);

		return link_html($links[$name], $wrap);
	}

	return false;
}
