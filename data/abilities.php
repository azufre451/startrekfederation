<?

class abilDescriptor
{
	public $abilDict = array();  
	
	public function __construct($id)
	{
		$r = mysql_query("SELECT * FROM pg_abilita_levels WHERE pgID = $id");
		echo mysql_error();
		while($s = mysql_fetch_array($r)){
			$this->abilDict[] = $r; 
			
		}
		
		echo "<pre>"; print_r($this->abilDict); echo "</pre>";
	}

	
}


$a = new abilDescriptor('1');
?>