<?php
include('functions.php');

define("DEBUG2SRT2ASS", FALSE);

// Display page ?
if(!try_get($_POST, 'send', $send) || $send != 'yes')
	sendHTML();

// set cookies
try_get($_POST, 'fontname', $fontname, 'Arial');
try_get($_POST, 'fontsize', $fontsize, '16');
try_get($_POST, 'topColor', $topColor, '#FFFFF9');
try_get($_POST, 'botColor', $botColor, '#F9FFF9');

try_get($_POST, 'forceBottom', $forceBottom, '0');


set_infinite_cookie('fontname', $fontname);
set_infinite_cookie('fontsize', $fontsize);
set_infinite_cookie('topColor', $topColor);
set_infinite_cookie('botColor', $botColor);
set_infinite_cookie('forceBottom', $forceBottom);

// Process
if(!DEBUG2SRT2ASS)
{
	if(!try_get($_FILES, 'top', $top)
		|| $top['error'] !== 0
		|| !try_get($_FILES, 'bot', $bot)
		|| $bot['error'] !== 0
		)
		sendHTML('Error while uploading. Try again.');

	$outputName = preg_replace('/(\.[a-zA-Z]{2,3})?\.srt$/', '.ass', $bot['name']);
	$contentTop = file_get_contents($top['tmp_name']);
	$contentBot = file_get_contents($bot['tmp_name']);
}
else
{
	$outputFile = 'file:///volume1/web/2srt2ass2/Hannibal.S01E01.HDTV.x264-LOL.ass';
	$contentTop = file_get_contents('file:///volume1/web/2srt2ass2/Hannibal.S01E01.HDTV.x264-LOL.en.srt');
	$contentBot = file_get_contents('file:///volume1/web/2srt2ass2/Hannibal.S01E01.HDTV.x264-LOL.fr.srt');
}

getStyles($fontname, $fontsize, $topColor, $botColor, $forceBottom, $styles, $stylesKeys);

cleanSRT($contentTop);
cleanSRT($contentBot);

$tree = array(/*array(
	'start' => '00:00:01.00',
	'end'   => '00:00:03.00',
	'type'  => 'Mid',
	'text'  => 'Merged with 2SRT2ASS\N(http://pas-bien.net/2srt2ass/)'
)*/);

parseAndAddSRT($contentTop, $tree, 'Top');
parseAndAddSRT($contentBot, $tree, 'Bot');

usort($tree,compare);



// Evrything ok, send file !

$outputData = "";

$outputData .= "[Script Info]\r\n";
$outputData .= "ScriptType: v4.00+\r\n";
$outputData .= "Collisions: Normal\r\n";
$outputData .= "PlayDepth: 0\r\n";
$outputData .= "Timer: 100,0000\r\n";
$outputData .= "Video Aspect Ratio: 0\r\n";
$outputData .= "WrapStyle: 0\r\n";
$outputData .= "ScaledBorderAndShadow: no\r\n";
$outputData .= "\r\n";
$outputData .= "[V4+ Styles]\r\n";
$outputData .= "Format: Name," . implode(',', $stylesKeys) . "\r\n";
foreach($styles as $styleName => $styleValues)
{
	$outputData .= "Style: " . $styleName . "," . implode(',', $styleValues) . "\r\n";
}
$outputData .= "\r\n";
$outputData .= "[Events]\r\n";
$outputData .= "Format: Layer, Start, End, Style, Name, MarginL, MarginR, MarginV, Effect, Text\r\n";

foreach ($tree as $dialogue)
{
	$outputData .= "Dialogue: 0,".getTimeStamp($dialogue['start']).",".getTimeStamp($dialogue['end']).",".$dialogue['type'].",,0000,0000,0000,,".$dialogue['text']."\r\n";
}

// Render

if(DEBUG2SRT2ASS)
{
	file_put_contents($outputFile, $outputData);
	echo date('r') . ":done !";
}
else
{
	header("Content-type: application/octet-stream;");
	header("Content-Disposition: attachment; filename=\"$outputName\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache"); 

	echo $outputData;
}

?>