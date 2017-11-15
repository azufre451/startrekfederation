<?php 
Class Marker
{
	public $posX;
	public $posY;
	public $placeString;
	
	public function __construct($x,$y,$place)
	{
		$this->posX = $x;
		$this->posY = $y;
		$this->placeString = $place;
	}
}

class MarkerCollection
{
	public $markers = array();
	
	public function addMarker($x,$y,$place)
	{
		if (isSet($this->markers[$x.';'.$y])) $this->markers[$x.';'.$y]->placeString .= '; '.$place;
		else $this->markers[$x.';'.$y] = new Marker($x,$y,$place);
	}
	
	public function getmark()
	{
	return $this->markers;
	}
	
	public function getmarkA()
	{
		$ar = array();
		foreach($this->markers as $marker)
		{
			$ar[] = array('posX' => $marker->posX, 'posY' => $marker->posY, 'placeString' => $marker->placeString);
		}
		return $ar;
	}
}?>