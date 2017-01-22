<?php

function try_get($search, $key, &$result, $default = null)
{
	if(array_key_exists($key, $search))
	{
		$result = $search[$key];
		return TRUE;
	}
	else
	{
		$result = $default;
		return FALSE;
	}
}

function set_infinite_cookie($name, $value)
{
	setcookie($name, $value, time() + (20 * 365 * 24 * 60 * 60));
}

function getStyles($fontname, $fontsize, $topColor, $botColor, $forceBottom, &$styles, &$keys)
{

	$specifics = array
		( 'Default' => array('PrimaryColour' => getColour('#FFFFFF') , "SecondaryColour" => getColour('#FFFFFF', null, 1.2), 'Alignment' => '2')
		, 'Top'     => array('PrimaryColour' => getColour($topColor) , "SecondaryColour" => getColour($topColor, null, 1.2), 'Alignment' => ($forceBottom == '1' ? '2' : '8'))
		, 'Mid'     => array('PrimaryColour' => getColour('#FFFF00') , "SecondaryColour" => getColour('#FFFFFF', null, 1.2), 'Alignment' => '5')
		, 'Bot'     => array('PrimaryColour' => getColour($botColor) , "SecondaryColour" => getColour($botColor, null, 1.2), 'Alignment' => '2')
		);
	
	$defaults = array(
		'Fontname' => $fontname,
		'Fontsize' => $fontsize,
		'PrimaryColour' => getColour('#FFFFFF'),
		'SecondaryColour' =>  getColour('#FFFFFF'),
		'OutlineColour' =>  getColour('#000000'),
		'BackColour' => getColour('#000000'),
		'Bold' => '-1',
		'Italic' => '0',
		'Underline' => '0',
		'StrikeOut' => '0',
		'ScaleX' => '100',
		'ScaleY' => '100',
		'Spacing' => '0',
		'Angle' => '0',
		'BorderStyle' => '1',
		'Outline' => '3',
		'Shadow' => '0',
		'Alignment' => '2',
		'MarginL' => '10',
		'MarginR' => '10',
		'MarginV' => '10',
		'Encoding' => '0',
	);
	
	$keys = array_keys($defaults);
	
	$styles = array();
	foreach($specifics as $key => $values)
	{
		$tmp = array();
		foreach($defaults as $dk => $dv)
		{
			$tmp[$dk] = array_key_exists($dk, $values) ? $values[$dk] : $dv;
		}
		$styles[$key] = $tmp;
	}
}

function getColour($color, $alpha = null, $change = null)
{
	if($alpha === null) $alpha = 0;
	if($change === null) $change = 1.0;
	if(!preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts))
		return "&H00FFFFFF";
	$out = '&H';
	$values = array($alpha);
	for($i = 3; $i >= 1; $i--)
	{
		$value = hexdec($parts[$i]);
		$value = intval(min(255, round($value * $change))); // 80/100 = 80%, i.e. 20% darker
		$values[] = $value;
	}
	for($i = 0; $i < count($values); $i++)
	{
		$out .= str_pad(dechex($values[$i]), 2, '0', STR_PAD_LEFT);
	}
	return strtoupper($out);
}

function sendHTML($error = null)
{
    include('html.php');
	die();
}

function cleanSRT(&$text)
{
	$text = strip_tags($text);

	$text .= "\n";

	$patterns = array();
	$replacements = array();
	// Windows to Unix
	$patterns[]     = '/\r\n/';
	$replacements[] = "\n";
	// Too much spaces
	$patterns[]     = '/[ \t]+\n/';
	$replacements[] = "\n";
	// Too much spaces
	$patterns[]     = '/\n\n\n+/';
	$replacements[] = "\n\n";
	// Tags
	$patterns[]     = '/\{\\\\pos\(\d+,\d+\)\}/';
	$replacements[] = "";
	
	$text = preg_replace($patterns, $replacements, $text);
	
	return null;
}

function getTimeStamp($str) {
	if($str[0] == '0') {
		return substr($str, 1);
	}
	return $str;
}

function parseAndAddSRT($text,&$tree,$type)
{
	$eregEvent = '/\d+\n(\d\d:\d\d:\d\d)[\.,](\d\d)\d --> (\d\d:\d\d:\d\d)[\.,](\d\d)\d\n(.+?(\n.+?)*)\n\n/';
	preg_match_all($eregEvent, $text, $matches);

	if(count($matches[0]) === 0)
		sendHTML('No subtitles found in SRT file for '.strtolower($type).'.');
	
	for ( $i = 0 ; $i < count($matches[0]) ; $i++ )
	{
		$tree[] = array(
			'start' => $matches[1][$i].'.'.$matches[2][$i],
			'end'   => $matches[3][$i].'.'.$matches[4][$i],
			'type'  => $type,
			'text'  =>  str_replace("\n", '\N', $matches[5][$i])
		);
	}
	
	return null;
}

function compare($a, $b)
{
	if($a['start'] < $b['start'])
		return -1;
	if($a['start'] > $b['start'])
		return 1;
	if($a['type'] < $b['type'])
		return -1;
	if($a['type'] > $b['type'])
		return 1;
	else
		return 0;
}

?>