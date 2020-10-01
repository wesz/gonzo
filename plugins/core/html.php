<?php

gonzo::import('functions');
gonzo::import('markup-txt');

global $HTML;
$HTML = array
(
	'indent' => 0,
	'inline' => array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'b', 'big', 'i', 'small', 'tt', 'abbr', 'acronym', 'cite', 'code', 'dfn', 'em', 'kbd', 'strong', 'samp', 'time', 'var', 'a', 'bdo', 'br', 'img', 'map', 'object', 'q', 'script', 'span', 'sub', 'sup', 'button', 'input', 'label', 'hr', 'br', 'li', 'u', 'del'),
	'single' => array('img', 'hr', 'br', 'input', 'meta', 'link', '!DOCTYPE')
);

function html_attr($attr = array())
{
	if (isset($attr['href']))
	{
		$attr['href'] = gonzo::url($attr['href']);

		if ($attr['href'] == gonzo::request_uri(true))
		{
			if ( ! isset($attr['class']))
			{
				$attr['class'] = 'active';
			} else
			{
				$attr['class'] .= ' active';
			}
		}
	} else if (isset($attr['src']))
	{
		$attr['src'] = substr($attr['src'], 0, 4) != 'data' ? gonzo::url($attr['src']) : $attr['src'];
	}

	if (is_array($attr))
	{
		$attrs = '';

		foreach($attr as $key => $value)
		{
			if ($value === true)
			{
				$attrs .= ' '.$key;
			} elseif (trim($value) !== '')
			{
				$attrs .= ' '.$key.'="'.$value.'"';
			}
		}
	} else
	{
		$attrs = ' '.$attr;
	}

	$attrs = trim($attrs);

	return ( ! empty($attrs) ? ' ' : '').$attrs;
}

function html_tag($tag, $attr = array())
{
	global $HTML;

	if ($attr === true)
	{
		if (in($HTML['single'], $tag))
		{
			return '';
		}

		return '</'.$tag.'>';
	}

	switch ($tag)
	{
		case 'script':
			if ( ! isset($attr['type'])) $attr['type'] = 'text/javascript';
		break;
		case 'link':
			if ( ! isset($attr['rel'])) $attr['rel'] = 'stylesheet';
		break;
		case 'img':
			if ( ! isset($attr['src']) || empty($attr['src'])) $attr['src'] = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
		break;
		case 'form':
			if ( ! isset($attr['accept-charset']) || empty($attr['accept-charset'])) $attr['accept-charset'] = gonzo::var('gonzo.charset');
		break;
	}

	return '<'.$tag.html_attr($attr).(in($HTML['single'], $tag) ? ' /' : '').'>'.(isset($attr['content']) ? $attr['content'] : '');
}

function html($tag, $content = '', $attr = NULL, $context_attr = NULL)
{
	global $HTML;

	if ($content === false)
	{
		return html_tag($tag, is_string($attr) ? array('class' => $attr) : $attr);
	} else if ($content === true)
	{
		return html_tag($tag, true);
	}

	if (is_assoc($content))
	{
		$context_attr = $attr;
		$attr = $content;
		$content = '';
	}

	if ( ! is_array($attr))
	{
		$value = $attr;
		$attr = array();

		switch ($tag)
		{
			case 'a':
			{
				$attr['href'] = $value;
			}
			break;
			case 'img':
			{
				$attr['src'] = $value;
			}
			break;
			default:
			{
				$attr['class'] = $value;
			}
			break;
		}
	}

	if (is_string($context_attr))
	{
		if (is_array($attr))
		{
			$attr['class'] = $context_attr;
		} else
		{
			$attr = array('class' => $context_attr);
		}
	}

	if (isset($attr['tag']))
	{
		$tag = $attr['tag'];

		unset($attr['tag']);
	}

	if (is_array($content) && ! is_assoc($content))
	{
		switch ($tag)
		{
			case 'table':
			{
				return html_table($content[0], $content[1], $attr);
			}
			break;

			case 'fieldset':
			{
				$args = func_get_args();

				return call_user_func_array('html_fieldset', $args);
			}
			break;

			case 'input':
			case 'textarea':

			break;

			default:
			{
				$tag_open = html_tag($tag, $attr);
				$tag_close = html_tag($tag, true);

				return $tag_open.implode($tag_close.$tag_open, $content).$tag_close;
			}
			break;
		}
	}

	switch ($tag)
	{
		case 'p':
			//if ( ! empty($content)) $content = markuptxt($content);
		break;
	}

	return html_tag($tag, $attr).$content.html_tag($tag, true);
}

function html_table($header, $rows, $col_callback = '', $col_defaults = array())
{
	$odd = true;

	$table = '<table cellspacing="0" width="100%">';
	$table .= '<thead><tr>';

	$header = assoc($header);
	$cols = count(array_keys($header));
	$width = ceil(100 / $cols);
	$col_count = 0;
	$col_width_total = 0;

	foreach ($header as $key => $heading)
	{
		$col_count++;

		$col_width = (isset($col_defaults[$key.'_width']) ? $col_defaults[$key.'_width'] : $width);
		$col_width_total += $col_width;

		if (($cols == 1 || $col_count == 2) && $col_width_total <= 100)
		{
			$col_width = 99;
		}

		if ( ! empty($col_width))
		{
			$col_width = ' width="'.$col_width.'%"';
		}

		$table .= '<th'.$col_width.'>'.$heading.'</th>';
	}

	$table .= '</tr></thead><tbody>';

	foreach ($rows as $index => $row_data)
	{
		$row_data = extend(assoc($row_data), $col_defaults);
		$table .= '<tr'.($odd ? ' class="odd"' : '').'>';

		foreach ($header as $key => $heading)
		{
			$col = (isset($row_data[$key]) ? $row_data[$key] : '');

			if ( ! empty($col_callback) && is_string($col_callback))
			{
				$col_key = $key;

				if (function_exists($col_callback))
				{
					$col = call_user_func_array($col_callback, array($col, $col_key, $row_data));
				}
			}

			$table .= '<td>'.$col.'</td>';
		}

		$odd = ! $odd;

		$table .= '</tr>';
	}

	$table .= '</tbody>';

	$table .= '<tfoot><tr>';

	foreach ($header as $key => $heading)
	{
		$table .= '<th>'.$heading.'</th>';
	}

	$table .= '</tr></tfoot>';
	$table .= '</table>';

	return $table;
}

function html_input($attr, $data = null, $context = '')
{
	$special = array('checkbox', 'radio', 'file');

	$defaults = array
	(
		'label' => __('Input', 'input'),
		'name' => '',
		'labelstyle' => '',
		'labelclass' => '',
		'hint' => '',
		'type' => 'text',
		'value' => '',
		'defaultvalue' => '',
		'class' => '',
		'id' => '',
		'name' => '',
		'rows' => '',
		'cols' => '',
		'autoresize' => false,
		'width' => '',
		'style' => '',
		'height' => '',
		'placeholder' => '',
		'autocomplete' => '',
		'tabsize' => '',
		'onblur' => '',
		'onchange' => '',
		'oncontextmenu' => '',
		'onfocus' => '',
		'oninput' => '',
		'oninvalid' => '',
		'onreset' => '',
		'onsearch' => '',
		'onselect' => '',
		'onsubmit' => '',
		'onclick' => '',
		'onkeydown' => '',
		'onkeyup' => '',
	);

	$attr = extend($defaults, $attr);

	$label = __($attr['label'], 'input');
	unset($attr['label']);

	if (empty($attr['name']))
	{
		$attr['name'] = filter_slug($label);
	}

	if (isset($attr['disabled']) && $attr['disabled'])
	{
		$attr['disabled'] = 'disabled';
	}

	$check_array = false;
	$array_name = '';
	$field_name = '';

	preg_match('/\[(.*?)\]/', $attr['name'], $matches);

	if (isset($matches[1]) && ! empty($matches[1]))
	{
		$array_name = str_replace('['.$matches[1].']', '', $attr['name']);
		$field_name = $matches[1];
		$check_array = true;
	}

	$hint = __($attr['hint'], 'input');
	unset($attr['hint']);

	if ($attr['type'] != 'submit' && $attr['type'] != 'button')
	{
		if ($data === null)
		{
			$data = $_POST;
		}

		if ( ! empty($data))
		{
			if (is_array($data))
			{
				if ($check_array && ! empty($field_name) && isset($data[$array_name]) && isset($data[$array_name][$field_name]))
				{
					$attr['value'] = $data[$array_name][$field_name];
				} else if (isset($data[$attr['name']]))
				{
					$attr['value'] = $data[$attr['name']];
				}
			} else
			{
				$attr['value'] = $data;
			}
		} else if ( ! empty($attr['defaultvalue']))
		{
			$attr['value'] = $attr['defaultvalue'];
		}
	}

	if (empty($attr['value']) && ! is_string($attr['value']))
	{
		$attr['value'] = '';
	}

	$attr['value'] = filter_santize($attr['value']);
	$attr['value'] = filter_strip_slashes($attr['value']);
	$attr['value'] = htmlspecialchars_decode($attr['value']);

	$html = '';

	if ($attr['type'] == 'text' || $attr['type'] == 'password' || $attr['type'] == 'hidden')
	{
		$html .= html
		(
			'input',
			'',
			$attr
		);
	} else if ($attr['type'] == 'textarea')
	{
		if ($attr['autoresize'])
		{
			$attr['onfocus'] = 'tf(this)';
			$attr['oninput'] = 'ti(this)';
			$attr['onchange'] = 'ti(this)';
			$attr['propertychange'] = 'ti(this)';
			$attr['onkeydown'] = 'tk()';
		}

		$value = $attr['value'];
		$type = $attr['type'];

		unset($attr['value']);
		unset($attr['type']);

		if ($attr['autoresize'] && is_bool($attr['autoresize']))
		{
			$attr['rows'] = count(explode("\n", $value));
		}

		$html .= html
		(
			'textarea',
			$value,
			$attr
		);

		$attr['type'] = $type;
	} else if ($attr['type'] == 'select')
	{
		$select = '';

		if (isset($attr['options']))
		{
			$group = '';
			$last_group = '';

			foreach ($attr['options'] as $option_key => $option_value)
			{
				if (is_array($option_value) && isset($option_value['group']) && $option_value['group'] != $group)
				{
					if ($group != '')
					{
						$select .= html_tag('optgroup', true);
					}

					$group = $option_value['group'];

					$select .= html_tag('optgroup', array('label' => __($group, 'input')));
				}

				if (is($option_value, 'name'))
				{
					$option_value = $option_value['name'];
				}

				$select .= html
				(
					'option',
					__($option_value, 'input'),
					array
					(
						'value' => $option_key,
						'selected' => ($option_key == $attr['value'] ? 'selected': '')
					)
				);
			}

			if ($group != '')
			{
				$select .= html_tag('optgroup', true);
			}

			unset($attr['options']);
		}

		unset($attr['value']);
		unset($attr['placeholder']);

		$html .= html
		(
			'select',
			$select,
			$attr
		);
	} else if (in($special, $attr['type']))
	{
		if ($attr['type'] == 'file')
		{
			$attr['onchange'] = 'fc()';

			if (isset($attr['multi']) && $attr['multi'])
			{
				$attr['name'] .= '[]';

				unset($attr['multi']);
			} else if (isset($attr['preview']) && $attr['preview'])
			{
				$html .= html
				(
					'img',
					'',
					array
					(
						'src' => (empty($attr['value']) ? 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==' : $attr['value']),
						'alt' => '',
						'width' => 64,
						'height' => 64
					)
				);

				unset($attr['preview']);
			}

			unset($attr['value']);
		} else
		{
			if ($attr['value'] == 'on')
			{
				$attr['checked'] = 'checked';
			} else if (isset($attr['checked']))
			{
				unset($attr['checked']);
			}

			unset($attr['value']);
		}

		unset($attr['placeholder']);

		$html = html
		(
			'input',
			'',
			$attr
		);
	}

	if (isset($attr['type']) && ($attr['type'] == 'submit' || $attr['type'] == 'button' || $attr['type'] == 'reset'))
	{
		unset($attr['placeholder']);

		$html = html
		(
			'button',
			__($label, 'input'),
			$attr
		);
	} else
	{
		$html .= valid_render($attr['name'], $context, true);

		$label_name = ( ! empty($label) ? '<span class="label-name">'.$label.'</span>' : '');

		if ($attr['type'] != 'hidden')
		{
			$attr['labelclass'] = 'label-'.$attr['type'].( ! empty($attr['labelclass']) ? ' ' : '').$attr['labelclass'];

			$html = '<label'.( ! empty($attr['id']) ? ' for="'.$attr['id'].'"' : '').( ! empty($attr['labelstyle']) ? ' style="'.$attr['labelstyle'].'"' : '').( ! empty($attr['labelclass']) ? ' class="'.$attr['labelclass'].'"' : '').'>'.(isset($attr['type']) && in($special, $attr['type']) && empty($hint) ? $html.$label_name : $label_name.$html);
		}

		if ( ! empty($hint) OR (isset($attr['type']) && $attr['type'] == 'select'))
		{
			$html .= '<span>'.$hint.'</span>';
		}

		if (isset($attr['type']) && $attr['type'] == 'file')
		{
			$html .= '<a href="" class="button" onclick="fr()" title="'.__('Remove attachment', 'input').'"></a>';
		}

		if (isset($attr['type']) && $attr['type'] != 'hidden')
		{
			$html .= '</label>';
		}
	}

	return $html;
}

function html_fieldset($name, $fields = array(), $after = '', $context = '', $attr = array())
{
	$fieldsetclass = '';
	$html = '';

	foreach ($fields as $field)
	{
		if (is_string($field))
		{
			$html .= $field;
		} else
		{
			$html .= html_input($field, null, $context);
		}
	}

	$html = valid_render('*', $context, true).$html;

	return '<fieldset'.html_attr($attr).'>'.( ! empty($name) ? '<legend>'.__($name, 'input').'</legend>' : '').$html.$after.'</fieldset>';
}
