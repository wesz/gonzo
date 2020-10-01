<?php

gonzo::import('array');
gonzo::import('html');

function form_sort($a, $b)
{
	return ($a['order'] < $b['order'] ? -1 : 1);
}

function register_form($id, $group, $label, $order = 5)
{
	$form = gonzo::var('gonzo.form');

	if ( ! is_array($form))
	{
		$form = array();
	}

	if ( ! isset($form[$id]))
	{
		$form[$id] = array();
	}

	$form[$id][$group] = array
	(
		'group' => $group,
		'label' => $label,
		'order' => $order,
		'fields' => array()
	);

	gonzo::var('gonzo.form', $form);
}

function register_form_field($id, $group, $name, $field, $order = 5)
{
	$form = gonzo::var('gonzo.form');

	if (isset($form[$id]) && isset($form[$id][$group]))
	{
		$form[$id][$group]['fields'][$name] = array
		(
			'name' => $name,
			'field' => $field,
			'order' => $order
		);
	}

	gonzo::var('gonzo.form', $form);
}

function form_render($id, $group = '', $context = '', $class = '')
{
	$form = gonzo::var('gonzo.form');

	$html = '';

	if (isset($form[$id]))
	{
		uasort($form[$id], 'form_sort');

		foreach ($form[$id] as $fieldset_group => $fieldset)
		{
			if (empty($group) || $group == $fieldset_group)
			{
				uasort($fieldset['fields'], 'form_sort');

				$html .= fieldset($fieldset['label'], array_collect_key(array_values($fieldset['fields']), 'field'), '', $context, array('class' => 'vr b1 ds '.$class));
			}
		}

		return $html;
	}

	return false;
}
