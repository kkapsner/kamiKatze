<?php

/**
 * Description of SoundWave
 *
 * @author kkapsner
 */
class SoundWave{
	/**
	 * Number of channels
	 * @var int
	 */
	public $channels = 1;
	/**
	 * Sample rate (1/s)
	 * @var int
	 */
	public $sampleRate = 44100;
	/**
	 * Bits per sample
	 * @var int
	 */
	public $bitsPerSample = 16;
	
	/**
	 * Bytes per sample
	 * @var int
	 */
	protected $sampleLength = false;
	/**
	 * Bytes per frame
	 * @var int
	 */
	protected $frameSize = false;
	/**
	 * Left shift for data to fit to bytes
	 * @var int
	 */
	protected $shift = false;
	public function fixParameter(){
		$this->sampleLength = $this->getSampleLength();
		$this->frameSize = $this->getFrameSize();
		$this->shift = $this->sampleLength * 8 - $this->bitsPerSample;
	}
	public function getSampleLength(){
		return floor(($this->bitsPerSample+7)/8);
	}
	public function getFrameSize(){
		return $this->channels * floor(($this->bitsPerSample+7)/8);
	}
	public function getDataBlockSize($numSamples){
		return $numSamples * $this->getFrameSize();
	}
	
	public function getSample($data){
		$pcmData = "";
		for ($i = 0; $i < $this->channels; $i++){
			$value = $data[$i] << $this->shift;
			for ($j = 0; $j < $this->sampleLength; $j++){
				$pcmData .= chr($value & 0xFF);
				$value >>= 8;
			}
		}
		return $pcmData;
	}
	
	public function getRIFFBlock($dataSize){
		return 'RIFF' . pack('V', $dataSize) . 'WAVE';
	}
	public function getFormatBlock(){
		$frameSize = $this->getFrameSize();
		$bytesPerSecond = $this->sampleRate * $frameSize;
		return 'fmt ' .
			pack('VvvV' . 'Vvv',
				16, 1, $this->channels, $this->sampleRate,
				$bytesPerSecond, $frameSize, $this->bitsPerSample
			);
	}
	public function getDataBlock($pcm){
		$sampleLength = ceil($this->bitsPerSample / 8);
		$shift = $sampleLength * 8 - $this->bitsPerSample;
		$pcmData = "";
		$samples = count($pcm);
		for ($t = 0; $t < $samples; $t++){
			$set = $pcm[$t];
			for ($i = 0; $i < $this->channels; $i++){
				$value = $set[$i] << $shift;
				for ($j = 0; $j < $sampleLength; $j++){
					$pcmData .= chr($value & 0xFF);
					$value >>= 8;
				}
			}
		}

		return 'data' . pack('V', $this->getDataBlockSize($pcm)) . $pcmData;
	}
	public function output($pcm){
		$dataBlock = $this->getDataBlock($pcm);
		$fmtBlock = $this->getFormatBlock();
		$riffBlock = $this->getRIFFBlock(strlen($dataBlock) + strlen($fmtBlock));
		return $riffBlock . $fmtBlock . $dataBlock;
	}
}

?>
