<?php

gonzo::import('html');

function div()
{
	$args = func_get_args();
	array_unshift($args, 'div');

	return call_user_func_array('html', $args);
}

function span()
{
	$args = func_get_args();
	array_unshift($args, 'span');

	return call_user_func_array('html', $args);
}

function object()
{
	$args = func_get_args();
	array_unshift($args, 'object');

	return call_user_func_array('html', $args);
}

function iframe()
{
	$args = func_get_args();
	array_unshift($args, 'iframe');

	return call_user_func_array('html', $args);
}

function h1()
{
	$args = func_get_args();
	array_unshift($args, 'h1');

	return call_user_func_array('html', $args);
}

function h2()
{
	$args = func_get_args();
	array_unshift($args, 'h2');

	return call_user_func_array('html', $args);
}

function h3()
{
	$args = func_get_args();
	array_unshift($args, 'h3');

	return call_user_func_array('html', $args);
}

function h4()
{
	$args = func_get_args();
	array_unshift($args, 'h4');

	return call_user_func_array('html', $args);
}

function h5()
{
	$args = func_get_args();
	array_unshift($args, 'h5');

	return call_user_func_array('html', $args);
}

function h6()
{
	$args = func_get_args();
	array_unshift($args, 'h6');

	return call_user_func_array('html', $args);
}

function p()
{
	$args = func_get_args();
	array_unshift($args, 'p');

	return call_user_func_array('html', $args);
}

function br()
{
	$args = func_get_args();
	array_unshift($args, 'br');

	return call_user_func_array('html', $args);
}

function blockquote()
{
	$args = func_get_args();
	array_unshift($args, 'blockquote');

	return call_user_func_array('html', $args);
}

function pre()
{
	$args = func_get_args();
	array_unshift($args, 'pre');

	return call_user_func_array('html', $args);
}

function abbr()
{
	$args = func_get_args();
	array_unshift($args, 'abbr');

	return call_user_func_array('html', $args);
}

function address()
{
	$args = func_get_args();
	array_unshift($args, 'address');

	return call_user_func_array('html', $args);
}

function cite()
{
	$args = func_get_args();
	array_unshift($args, 'cite');

	return call_user_func_array('html', $args);
}

function code()
{
	$args = func_get_args();
	array_unshift($args, 'code');

	return call_user_func_array('html', $args);
}

function del()
{
	$args = func_get_args();
	array_unshift($args, 'del');

	return call_user_func_array('html', $args);
}

function dfn()
{
	$args = func_get_args();
	array_unshift($args, 'dfn');

	return call_user_func_array('html', $args);
}

function em()
{
	$args = func_get_args();
	array_unshift($args, 'em');

	return call_user_func_array('html', $args);
}

function img()
{
	$args = func_get_args();
	array_unshift($args, 'img');

	return call_user_func_array('html', $args);
}

function ins()
{
	$args = func_get_args();
	array_unshift($args, 'ins');

	return call_user_func_array('html', $args);
}

function kbd()
{
	$args = func_get_args();
	array_unshift($args, 'kbd');

	return call_user_func_array('html', $args);
}

function q()
{
	$args = func_get_args();
	array_unshift($args, 'q');

	return call_user_func_array('html', $args);
}

function samp()
{
	$args = func_get_args();
	array_unshift($args, 'samp');

	return call_user_func_array('html', $args);
}

function small()
{
	$args = func_get_args();
	array_unshift($args, 'small');

	return call_user_func_array('html', $args);
}

function strong()
{
	$args = func_get_args();
	array_unshift($args, 'strong');

	return call_user_func_array('html', $args);
}

function sub()
{
	$args = func_get_args();
	array_unshift($args, 'sub');

	return call_user_func_array('html', $args);
}

function sup()
{
	$args = func_get_args();
	array_unshift($args, 'sup');

	return call_user_func_array('html', $args);
}

function _var()
{
	$args = func_get_args();
	array_unshift($args, 'var');

	return call_user_func_array('html', $args);
}

function b()
{
	$args = func_get_args();
	array_unshift($args, 'b');

	return call_user_func_array('html', $args);
}

function i()
{
	$args = func_get_args();
	array_unshift($args, 'i');

	return call_user_func_array('html', $args);
}

function dl()
{
	$args = func_get_args();
	array_unshift($args, 'dl');

	return call_user_func_array('html', $args);
}

function dt()
{
	$args = func_get_args();
	array_unshift($args, 'dt');

	return call_user_func_array('html', $args);
}

function dd()
{
	$args = func_get_args();
	array_unshift($args, 'dd');

	return call_user_func_array('html', $args);
}

function ol()
{
	$args = func_get_args();
	array_unshift($args, 'ol');

	return call_user_func_array('html', $args);
}

function ul()
{
	$args = func_get_args();
	array_unshift($args, 'ul');

	return call_user_func_array('html', $args);
}

function li()
{
	$args = func_get_args();
	array_unshift($args, 'li');

	return call_user_func_array('html', $args);
}

function fieldset()
{
	$args = func_get_args();

	if (isset($args[1]) && is_array($args[1]) && ! is_assoc($args[1]))
	{
		return call_user_func_array('html_fieldset', $args);
	}

	array_unshift($args, 'fieldset');

	return call_user_func_array('html', $args);
}

function form()
{
	$args = func_get_args();
	array_unshift($args, 'form');

	return call_user_func_array('html', $args);
}

function label()
{
	$args = func_get_args();
	array_unshift($args, 'label');

	return call_user_func_array('html', $args);
}

function legend()
{
	$args = func_get_args();
	array_unshift($args, 'legend');

	return call_user_func_array('html', $args);
}

function table()
{
	$args = func_get_args();

	if (isset($args[1]) && is_array($args[1]) && ! is_assoc($args[1]))
	{
		return call_user_func_array('html_table', $args);
	}

	array_unshift($args, 'table');

	return call_user_func_array('html', $args);
}

function caption()
{
	$args = func_get_args();
	array_unshift($args, 'caption');

	return call_user_func_array('html', $args);
}

function tbody()
{
	$args = func_get_args();
	array_unshift($args, 'tbody');

	return call_user_func_array('html', $args);
}

function tfoot()
{
	$args = func_get_args();
	array_unshift($args, 'tfoot');

	return call_user_func_array('html', $args);
}

function thead()
{
	$args = func_get_args();
	array_unshift($args, 'thead');

	return call_user_func_array('html', $args);
}

function tr()
{
	$args = func_get_args();
	array_unshift($args, 'tr');

	return call_user_func_array('html', $args);
}

function th()
{
	$args = func_get_args();
	array_unshift($args, 'th');

	return call_user_func_array('html', $args);
}

function td()
{
	$args = func_get_args();
	array_unshift($args, 'td');

	return call_user_func_array('html', $args);
}

function article()
{
	$args = func_get_args();
	array_unshift($args, 'article');

	return call_user_func_array('html', $args);
}

function aside()
{
	$args = func_get_args();
	array_unshift($args, 'aside');

	return call_user_func_array('html', $args);
}

function canvas()
{
	$args = func_get_args();
	array_unshift($args, 'canvas');

	return call_user_func_array('html', $args);
}

function details()
{
	$args = func_get_args();
	array_unshift($args, 'details');

	return call_user_func_array('html', $args);
}

function figcaption()
{
	$args = func_get_args();
	array_unshift($args, 'figcaption');

	return call_user_func_array('html', $args);
}

function figure()
{
	$args = func_get_args();
	array_unshift($args, 'figure');

	return call_user_func_array('html', $args);
}

function footer()
{
	$args = func_get_args();
	array_unshift($args, 'footer');

	return call_user_func_array('html', $args);
}

function _header()
{
	$args = func_get_args();
	array_unshift($args, 'header');

	return call_user_func_array('html', $args);
}

function hgroup()
{
	$args = func_get_args();
	array_unshift($args, 'hgroup');

	return call_user_func_array('html', $args);
}

function menu()
{
	$args = func_get_args();
	array_unshift($args, 'menu');

	return call_user_func_array('html', $args);
}

function nav()
{
	$args = func_get_args();
	array_unshift($args, 'nav');

	return call_user_func_array('html', $args);
}

function section()
{
	$args = func_get_args();
	array_unshift($args, 'section');

	return call_user_func_array('html', $args);
}

function summary()
{
	$args = func_get_args();
	array_unshift($args, 'summary');

	return call_user_func_array('html', $args);
}

function _time()
{
	$args = func_get_args();
	array_unshift($args, 'time');

	return call_user_func_array('html', $args);
}

function mark()
{
	$args = func_get_args();
	array_unshift($args, 'mark');

	return call_user_func_array('html', $args);
}

function audio()
{
	$args = func_get_args();
	array_unshift($args, 'audio');

	return call_user_func_array('html', $args);
}

function video()
{
	$args = func_get_args();
	array_unshift($args, 'video');

	return call_user_func_array('html', $args);
}

function a()
{
	$args = func_get_args();
	array_unshift($args, 'a');

	return call_user_func_array('html', $args);
}

function button()
{
	$args = func_get_args();
	array_unshift($args, 'button');

	return call_user_func_array('html', $args);
}

function input($name, $attr = array(), $data = NULL, $prefix = '')
{
	$attr['label'] = $name;

	return html_input($attr, $data, $prefix);
}

function textarea($name, $attr = array(), $data = NULL, $prefix = '')
{
	$attr['label'] = $name;
	$attr['type'] = 'textarea';

	return html_input($attr, $data, $prefix);
}

function script()
{
	$args = func_get_args();
	array_unshift($args, 'script');

	return call_user_func_array('html', $args);
}

function stylesheet()
{
	$args = func_get_args();
	array_unshift($args, 'link');

	return call_user_func_array('html', $args);
}

function viewmeta($name)
{
	return gonzo::var('view.'.$name);
}
