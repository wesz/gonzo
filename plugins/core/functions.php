<?php

function minify($code)
{
	// workaroud for some css minify bug
	$code = str_replace(array('[ ]', '( )'), array('[$$$]', '($$$)'), $code);
	// strip comments
	$code = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $code);

	$delimiters = array
	(
		'; ' => ';',
		': ' => ':',
		', ' => ',',
		' {' => '{',
		'{ ' => '{',
		' }' => '}',
		'} ' => '}',
		//' (' => ' (', breaks css media queries
		'( ' => '(',
		' )' => ')',
		') ' => ')',
		' [' => '[',
		'[ ' => '[',
		' ]' => ']',
		'] ' => ']'
	);

	$operators = array
	(
		' = ' => '=',
		' + ' => '+',
		' - ' => '-',
		' / ' => '/',
		' * ' => '*',
		' == ' => '==',
		' || ' => '||',
		' && ' => '&&',
		' > ' => '>',
		' >= ' => '>=',
		' < ' => '<',
		' <= ' => '<='
	);

	$code = str_replace(array_keys($delimiters), array_values($delimiters), $code);
	$code = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $code);

	return str_replace(array('[$$$]', '($$$)'), array('[ ]', '( )'), $code);
}

function font_size_scale($font_size, $font_size_max, $scale_min, $scale_max, $value)
{
	return $value == $font_size ? $font_size : ($value / $scale_max) * ($font_size_max - $font_size) + $font_size;
}

function ui_toggle($type, $prefix, $value, $label = null, $name = null)
{
	if ($label === null)
	{
		$label = '&nbsp;';
	}

	if ($name === null)
	{
		$name = filter_slug($value. '_-');
	}

	return '<div class="filter-toggle ui-toggle-after ui-plus"><input type="'.$type.'" id="'.$prefix.'_'.filter_slug($value. '_-').'" name="'.$name.'"'.(gonzo::input('get', $name) ? ' checked="checked"' : '').'><label for="'.$prefix.'_'.filter_slug($value. '_').'">'.( ! empty($label) ? '<span class="metalink">'.$label.'</span>' : '').'</label></div>';
}

function ui_index_by($index_id, $array, $attr = '', $callback = false, $cols = 3)
{
	$index = array();
	$data;

	if ( ! empty($attr))
	{
		$data = array_collect_key($array, $attr);
	} else
	{
		$data = $array;
	}

	foreach ($data as $value)
	{
		if ($attr == 'name')
		{
			$value = trim(filter_slug($value, ''), '-_/.,');
		}

		// tmp addon for url index, move it callback or filter
		$index_value = substr($value, 0, 4) == 'www.' ? substr($value, 4) : $value;

		$letter = strtoupper(substr($index_value, 0, 1));

		if ( ! isset($index[$letter]))
		{
			$index[$letter] = array();
		}

		$index[$letter][] = $value;
		$index[$letter] = array_unique($index[$letter]);
	}

	ksort($index);

	$col_count = 0;
	$col_total_count = 0;
	$html = '<form method="get" action="#content">';

	foreach ($index as $key => $values)
	{
		if ($col_count == $cols)
		{
			$html .= '</div>';
			$col_count = 0;
		}

		$col_count++;
		$col_total_count++;

		if ($col_count == 1)
		{
			$html .= '<div class="g gg g'.$cols.' svv1">';
		}

		$html .= '<div class="w1"><h3 class="fh1">'.$key.'</h3><ul>';

		foreach ($values as $value)
		{
			if ( ! empty($callback))
			{
				$html .= '<li>'.call_user_func_array($callback, array($value)).'</li>';
			} else
			{
				$html .= '<li>'.ui_toggle('checkbox', 'id', $value, $value).'</li>';
			}
		}

		$html .= '</div>';
	}

	$html .= '</div>';

	return $html;
}

function ui_table_columns($col, $key, $row)
{
	if ($key == 'id')
	{
		return ui_toggle('checkbox', 'id', $row['id']);
	} else if ($key == 'date' OR $key == 'modified')
	{
		$col = filter_time_format($col);
	} else if ($key == 'content')
	{
		$col = markuptxt($col);
	}

	return $col;
}

function ui_table($cols, $rows, $widths = array())
{
	if (empty($widths))
	{
		$widths = array
		(
			'id_width' => 1
		);
	}

	return div(table($cols, $rows, 'ui_table_columns', $widths), 'vr sv1 bv1');
}
