<?php

gonzo::import('css');

function css_get($key)
{
	global $css;

	return $css[$key];
}

function css_set($key, $value)
{
	global $css;

	$css[$key] = $value;
}

function css($selector, $props = array(), $context = '')
{
	global $css;

	foreach ($props as $key => $value)
	{
		$css['__rules'][$context][$selector][$key] = $value;
	}
}
