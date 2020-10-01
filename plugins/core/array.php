<?php

function array_collect_key($array, $key)
{
	$output = array();

	if (is_assoc($array))
	{
		if (is($array, $key))
		{
			$output[] = $array[$key];
		}

		return $output;
	}

	$count = count($array);

	for ($i = 0; $i < $count; $i++)
	{
		if (is($array[$i], $key))
		{
			$output[] = $array[$i][$key];
		}
	}

	return $output;
}

function array_prefix_key($array, $prefix, $filter = array())
{
	$output = array();

	if (is_assoc($array))
	{
		foreach ($array as $key => $value)
		{
			if (notin($filter, $key))
			{
				$output[$prefix.$key] = $value;
			}
		}

		return $output;
	}

	$count = count($array);

	for ($i = 0; $i < $count; $i++)
	{
		$output[] = $array[$i];

		foreach ($array[$i] as $key => $value)
		{
			if ($key != 'id' && notin($filter, $key))
			{
				$key = $prefix.$key;
			}

			$output[$i] += [ "$key" => $value ];
		}
	}

	return $output;
}
