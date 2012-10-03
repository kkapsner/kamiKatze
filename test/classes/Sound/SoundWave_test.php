<?php
/**
 * Test file for generating wave files.
 * 
 * @author Korbinian Kapsner
 */

/**
 * include framework
 */
include_once("../../../autoload.php");

$w = new SoundWave();
$w->channels = 1;
$w->bitsPerSample = 16;
$w->sampleRate = 44100;

header("Content-Type: audio/wav");
header('Content-Disposition: attachment; filename="SoundWave_test.wav"');
$data = array();
$numSamples = 0;
foreach ($_GET['frequency'] as $s => $f){
	$numSamples += $w->sampleRate * $_GET['duration'][$s];
}

$dataSize = $w->getDataBlockSize($numSamples);
echo
	$w->getRIFFBlock($dataSize + 4 + 16) .
	$w->getFormatBlock() .
	'data' . pack('V', $dataSize);

$a = pow(2, $w->bitsPerSample - 2) - 1;
$pi2 = 2 * pi();
foreach ($_GET['frequency'] as $s => $f){
	$samples = $w->sampleRate * $_GET['duration'][$s];
	for ($i = 0; $i < $samples; $i++){
		# sinusförmig
		echo pack('v', $a - floor($a * cos($f * $i * $pi2 / $w->sampleRate)));
		
		# sägezahn
		#$tau = ($f * $i/ $w->sampleRate);
		#echo pack('v', floor($a * ($tau-floor($tau))));
		
		# dreieck
		#$tau = ($f * $i/ $w->sampleRate);
		#echo pack('v', floor($a * abs(($tau-floor($tau)*2 - 1))));
	}
}

?>
