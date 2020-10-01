<?php

gonzo::import('functions');

global $css;

$css = array
(
	'font-size' => 16,
	'line-height' => 1.5,
	'font-unit' => 'em',
	'font-scales' => array
	(
		'minor-third' => 1.2,
		'major-third' => 1.25,
		'fourth' => 1.333,
		'fifth' => 1.5,
		'fibonacci' => 1.618,
		'minor-seventh' => 1.778,
		'major-seventh' => 1.875,
		'octave' => 2.0
	),
	'__rules' => array(),
	'__vendors' => array('-webkit-', '-moz-', '-ms-', '-o-'),
	'__prefix' => array
	(
		'border-radius' => '',
		'user-select' => '',
		'box-sizing' => '',
		'animation-play-state' => '',
		'animation-duration' => '',
		'animation-timing-function' => '',
		'animation-fill-mode' => '',
		'animation-delay' => '',
		'animation-name' => '',
		'transform-origin' => '',
		'animation-play-state' => '',
		'animation-play-state' => '',
		'animation-play-state' => '',
		'animation-play-state' => '',
		'animation-play-state' => '',
		'animation-play-state' => ''
	)
);

foreach($css as $key => $value)
{
	if (substr($key, 2, 2) != '__')
	{
		$key = str_replace('-', '_', $key);

		$$key = $value;
	}
}

function css_unit($key, $value, $unit = '', $overwrite = false)
{
	if (is_array($value))
	{
		foreach ($value as $index => $v)
		{
			$value[$index] = css_unit($key, $v, $unit, $overwrite);
		}

		return implode(' ', $value);
	}

	if ($key == 'content')
	{
		$value = '\''.__($value, 'css').'\'';
	}

	if (empty($value) OR $value == '!important' OR in(array('line-height', 'z-index', 'opacity'), $key))
	{
		return $value;
	}

	if (is_numeric($value))
	{
		return $value.$unit;
	}

	foreach (array('px', 'em', '%') as $u)
	{
		if (substr($value, -strlen($u), strlen($u)) == $u)
		{
			if ($overwrite)
			{
				return substr($value, 0, -2).$unit;
			}

			return $value;
		}
	}

	return $value;
}

function css_rule(array $rule, $options = array())
{
	global $css;

	$cssrule = '';

	foreach ($rule as $selector => $props)
	{
		$selectorparts = explode('!', $selector);

		if (count($selectorparts) > 1)
		{
			$pseudoselectors = explode(',', trim(array_shift($selectorparts)));

			$selector = array();

			foreach ($pseudoselectors as $pseudoselector)
			{
				$pseudoselector = trim($pseudoselector);

				foreach ($selectorparts as $pseudoclass)
				{
					$selector[] = trim($pseudoselector).trim($pseudoclass);
				}
			}

			$selector = implode(',', $selector);
		}

		$selector = explode(',', $selector);
		$heading = false;

		foreach ($selector as $i => $sel)
		{
			$selector[$i] = trim($sel);

			$selector_parts = preg_split('/( |>|\+)/', $selector[$i]);
			$selector_last = trim(array_pop($selector_parts));

			if (in_array($selector_last, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6')))
			{
				$heading = true;
			}
		}

		$selector = implode(', ', $selector);

		if (is_array($props))
		{
			$italic = false;
			$bold = false;
			$normal = false;
			$family = false;

			foreach ($props as $key => $value)
			{
				$val = str_replace('!important', '', is_array($value) ? implode(' ', $value) : $value);
				$val = trim($val);

				if ($key == 'font-weight')
				{
					if ($val == 'bold')
					{
						$bold = true;
					} else if ($val == 'normal')
					{
						$normal = true;
					}
				}

				if ($key == 'font-style' && $val == 'italic')
				{
					$italic = true;
				}

				if ($key == 'font-family')
				{
					$family = true;
				}
			}

			if ( ! $family)
			{
				$font_type = $heading ? 'font_heading_' : 'font_text_';

				if ($bold && isset($options[$font_type.'bold']) && ! empty($options[$font_type.'bold']))
				{
					$props['font-family'] = '\''.$options[$font_type.'bold'].'\'';
				} else if ($italic && isset($options[$font_type.'italic']) && ! empty($options[$font_type.'italic']))
				{
					$props['font-family'] = '\''.$options[$font_type.'italic'].'\'';
				} else if ($normal && isset($options[$font_type.'regular']) && ! empty($options[$font_type.'regular']))
				{
					$props['font-family'] = '\''.$options[$font_type.'regular'].'\'';
				}
			}

			$cssrule .= $selector.' {';

			foreach ($props as $key => $value)
			{
				$prefixes = array('');

				if (isset($css['__prefix'][$key]))
				{
					if ( ! empty($css['__prefix'][$key]))
					{
						$prefixes += $css['__prefix'][$key];
					} else
					{
						$prefixes += $css['__vendors'];
					}
				}

				$shorthand = str_replace(array('-left', '-top', '-right', '-bottom'), '', $key);

				if (isset($css[$shorthand.'-unit']))
				{
					$value = css_unit($shorthand, $value, $css[$shorthand.'-unit']);
				} else
				{
					$value = css_unit($key, $value, $css['font-unit']);
				}

				if ($family && $key == 'font-family' && substr($value, 0, 8) == 'monovoid')
				{
					$font_type = $heading ? 'font_heading_' : 'font_text_';

					if ($bold && isset($options[$font_type.'bold']) && ! empty($options[$font_type.'bold']))
					{
						$value = '\''.$options[$font_type.'bold'].'\', '.$value;
					} else if ($italic && isset($options[$font_type.'italic']) && ! empty($options[$font_type.'italic']))
					{
						$value = '\''.$options[$font_type.'italic'].'\', '.$value;
					} else if (isset($options[$font_type.'regular']) && ! empty($options[$font_type.'regular']))
					{
						$value = '\''.$options[$font_type.'regular'].'\', '.$value;
					}
				}

				foreach ($prefixes as $prefix)
				{
					$cssrule .= ' '.$prefix.$key.': ';

					$cssrule .= $value;
					$cssrule .= ';';
				}
			}

			$cssrule .= ' }';
		}
	}

	return $cssrule;
}

function css_font_round($x)
{
	$p = 0.05;

	$x = round($x, 2);
	$r = ($x / $p);
	$r2 = ceil($r) - $r;
	$a = round($x, 1);

	if ($r2 > 0 && $r2 < 0.5)
	{
		$a = $a + 0.05;
	}

	return $a;
}

function css_font_rhythm($level, $scale = 'fourth', $before = 0, $after = 0, $cap_height = 0.68, $margin_only = false)
{
	global $css;

	$font = array();

	$height = ceil($css['font-size'] * $css['line-height']);
	$size_factor = pow($css['font-scales'][$scale], $level);

	$font['font-size'] = ceil($css['font-size'] * $size_factor);

	$size = round(($font['font-size'] + 0.001) / $height);

	$font['line-height'] = round($height * $size);

	$font['padding-top'] = round(($font['line-height'] - ($font['font-size'] * $cap_height)) / 2);
	$font['margin-bottom'] = ($font['padding-top'] > $height ? 2 : 1) * $height - $font['padding-top'];

	if ($level)
	{
		if ( ! $margin_only)
		{
			$font['padding-top'] += $before * $height;
		}

		$font['margin-bottom'] += $after * $height;
	}

	if ($css['font-unit'] == 'em')
	{
		$fs = ceil($css['font-size'] * $size_factor);

		if ($margin_only)
		{
			$font['margin-bottom'] += $font['padding-top'];
			$font['padding-top'] = $before * $height;
		}

		$font['line-height'] = array($font['line-height'] / $fs, '!important');
		$font['padding-top'] = array($font['padding-top'] / $fs, '!important');
		$font['margin-bottom'] = array($font['margin-bottom'] / $fs, '!important');
		$font['font-size'] = array($font['font-size'] / $css['font-size'], '!important');
	}

	return $font;
}

function css_font_viewport($size, $screen_font_min = 16, $screen_font_max = 32, $screen_size_min = 320, $screen_size_max = 2560, $unit = 'em')
{
	if ($size < $screen_size_min)
	{
		$size = $screen_size_min;
	} else if ($size > $screen_size_max)
	{
		$size = $screen_size_max;
	}

	$font = $screen_font_min;

	if ($unit == 'em')
	{
		$font = ceil($screen_font_min + ($screen_font_max - $screen_font_min) * (($size - $screen_size_min) / ($screen_size_max - $screen_size_min)));

		if ($font % 2 != 0)
		{
			$font += 1;
		}
	} else if ($unit == '%')
	{
		$percent_min = 100;
		$percent_max = 175;

		$font = $percent_min + ((1 - ($screen_size_max - $size) / ($screen_size_max - $screen_size_min)) * ($percent_max - $percent_min));

		if ($font % 2 != 0)
		{
			$font += 1;
		}

		return $font;
	}

	if ($font < $screen_font_min)
	{
		$font = $screen_font_min;
	} else if ($font > $screen_font_max)
	{
		$font = $screen_font_max;
	}

	return $font;
}

function css_render($options = array())
{
	global $css;

	$cssrules = '';

	foreach ($css['__rules'] as $context => $selectors)
	{
		if ( ! empty($context))
		{
			$cssrules .= $context.(substr($context, 0, 2) == '/*' ? '' : ' {')."\n";
		}

		foreach ($selectors as $selector => $props)
		{
			$cssrules .= css_rule(array($selector => $props), $options)."\n";
		}

		if ( ! empty($context))
		{
			$cssrules .= (substr($context, 0, 2) == '/*' ? $context : '}')."\n\n";
		}
	}

	return gonzo::filtered('css.render', $cssrules);
}
