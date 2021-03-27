<?php

class abilDescriptor
{

	public static $transArray = array('GEN'=>'Ab. Generali','COMB'=>'Ab. Combattimento','ATT'=>'Ab. Attitudinali','SPE'=>'Ab. Speciali','TEC'=>'Ab. Tecniche','SCI'=>'Ab. Scientifiche','ABIL'=>'Caratteristiche');
	
	
	public static function translate($t){return self::$transArray[$t];}
	public static function getAbil($id){ $ide=addslashes($id); $res = mysql_fetch_assoc(mysql_query("SELECT * FROM pg_abilita WHERE abID = '$ide' LIMIT 1")); if(!mysql_error()) return $res; }
	public static function getAllAbil(){
		$res = mysql_query("SELECT * FROM pg_abilita ORDER BY abName");
		$r=array();

		
		while($ras = mysql_fetch_assoc($res)){
			if (!array_key_exists($ras['abClass'], $r)) $r[$ras['abClass']] = array();
			$r[$ras['abClass']][]=$ras; 

		}
		return $r;
	}

	public $abilDict = array();  
	private $abilTable = array('0'=>array(), '1'=>array(), '2'=>array() );
	private $user;
	
	public function __construct($id) 
	{
	
		$this->read_abilTable(); 
		$this->user = $id;
		$this->populate();
		
	}
	
	private function populate(){
		$id = $this->user;

		$this->userUpgradePoints = PG::getSomething($id,'upgradePoints');
		$r = mysql_query("SELECT abID, abName, abDescription, abImage, abClass, abDiff,abDepString, (SELECT value FROM pg_abilita_levels WHERE pg_abilita_levels.abID = pg_abilita.abID AND pgID = $id) as value, abLevelDescription_1,abLevelDescription_2,abLevelDescription_3,abLevelDescription_4,abLevelDescription_5 FROM pg_abilita WHERE 1");
		
		while($s = mysql_fetch_assoc($r)){
			$this->abilDict[$s['abID']] = $s;
			$this->abilDict[$s['abID']]['levelperc'] = ceil((float)($s['value'])/15*100);
			$this->abilDict[$s['abID']]['leveldesc'] = ($s['value'] > 0) ? $s['abLevelDescription_'.ceil($s['value']/3)] : 'Abilità attivata';
		}
	}

	public function getCars(){

		$tr=array();
		foreach($this->abilDict as $dicter){
			if ($dicter['abClass'] == 'ABIL'){
				$tr[$dicter['abID']] = $dicter['value'];
			}
		}
		return $tr;
	}

	private function read_abilTable()
	{
		$file_handle = fopen("data/abilities_progression.txt", "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
			$l = preg_split("/[\t]/", trim($line));
			if($line[0] == '#') continue;
			
			$this->abilTable['0'][$l[0]] = $l[1];
			$this->abilTable['1'][$l[0]] = $l[2];
			$this->abilTable['2'][$l[0]] = $l[3];			
			$this->abilTable['3'][$l[0]] = $l[4];			
		}
		fclose($file_handle);
		
		
		$file_handle = fopen("data/char_progression.txt", "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
			$l = preg_split("/[\t]/", trim($line));
			if($line[0] == '#') continue;
			
			$this->abilTable['HT'][$l[0]] = $l[1];
			$this->abilTable['DX'][$l[0]] = $l[2];
			$this->abilTable['IQ'][$l[0]] = $l[3];			
			$this->abilTable['PE'][$l[0]] = $l[4];	
			$this->abilTable['WP'][$l[0]] = $l[5];						
		}
		fclose($file_handle);
	}


	public function explainDependencies($abID){
		$strr = $this->abilDict[$abID]['abDepString'];
		$krr=array();

		

		if($strr != '')
		{
			$t = explode('__',$strr);
			
			foreach($t as $atp){
				$etp=explode('#',$atp);
				
				$modifier=(int)$etp[1]/100 * $this->abilDict[$etp[0]]['value'];

				$krr[] = array($this->abilDict[$etp[0]],$etp[1],$modifier);
			}
		}
		return $krr;
	}

	public function reRollDice($abID,$val,$mod){
		
		$ara = $this->explainDice($abID,$mod,($val == 99));
		$valU = ($val == 99) ? "20" : $val;
		$valL = ($val == 99) ? "*" : $val; 
		return array('v'=>$valL,'threshold'=>$ara['vs'],'mod'=>$mod,'outcome'=>$ara['ara'][(string)($valU)]);
	}

	public function rollDice($abID,$lucky=0){

		$val = real_mt_rand(1,20);

		if($val == 1)
			$val = (real_mt_rand(1,10) > 5) ? 1 : real_mt_rand(1,20);
	
		$ara = $this->explainDice($abID,0,$lucky);

		return array('v'=>$val,'threshold'=>$ara['vs'],'outcome'=>$ara['ara'][(string)($val)]);
	}

	public function explainDice($abID,$mod=0,$lucky=0){

		if(!is_null($this->abilDict[$abID]['value']))
		{
			$myval = $this->abilDict[$abID]['value'];
			$strr = "<li> Livello Abilità: ".$myval.'</li>';
		}
		else{

			$myval= -3*($this->abilDict[$abID]['abDiff']);
			$strr="<li> Malus per abilità non ancora attivata: ".$myval.'</li>';
		}
		
		$sumValor=0;
		foreach($this->explainDependencies($abID) as $dependency){
			$ab=$dependency[0];
			$perc_of_dependency = (int)$dependency[1];

			$valT=$ab['value']*($perc_of_dependency)/100;
			$sumValor+=$valT;
			$strr.='<li> '.$ab['abName'].' (influisce al '.$perc_of_dependency.'%): '.$valT.'</li>';
		}

		if (ceil($sumValor)) 
			$totValor = $myval+ceil($sumValor)+$mod;
		else{
			$totValor = $myval+0+$mod;
			//$strr.='<li>Bonus per lancio caratteristica singola: 2</li>';
		}

		$ara=array(1=> (($lucky) ? 'DF' : 'FC'));
		for ($i = 2; $i <= 20; $i++){
			if ($lucky){ $ara[$i] = 'DF';}
			elseif ($i < $totValor) $ara[$i] = 'S';
			elseif ( $i == $totValor) $ara[$i] = 'SC';
			else {$ara[$i] = "F";}
		}

		if ($totValor > 20){
			for ($i = 20; $i <= $totValor; $i++){
				$ara[40-$i] = 'SC';
			}
		}

		$DefStrr = 'Valore di soglia: '.$totValor.'<hr /><ul>'.$strr.'</ul>';
		return array('ara'=>$ara,'vs'=>$totValor,'string'=> $DefStrr);
	}  
	
	public function fromToAbil($from,$to,$abID)
	{
		
		$costTable = (($this->abilDict[$abID]['abClass'] == 'ABIL') ? $this->abilTable[$abID] : $this->abilTable[$this->abilDict[$abID]['abDiff']]);
		
		$cost=0;
		

		if ($from == $to) return 0;

		if ($from < $to){
		
		for($i=$from+1; $i<=$to; $i++ )
		{ 
			$cost+=$costTable[$i];

		} 
		return $cost;
		}
		
		if ($from > $to)
		{
			$temp = $from;
			$from = $to;
			$to = $temp;
			
			for($i=$from+1; $i<=$to; $i++ )
			{
				$cost+=$costTable[$i];
			} 
			return 0-$cost;
		}
	}
	
	
	public function calculateVariationCost($differential)
	{
		$totalCost = 0;
		foreach($differential as $amender){
			 
			$abID = $amender[0];
			$amend = $amender[1]; 
			 
			if($this->abilDict[$abID]['value'] == NULL)
			{
				$totalCost += ((int)($this->abilDict[$abID]['abDiff']) +1);
				$totalCost += $this->fromToAbil(0,$amend, $abID);
			}
			else{
				$totalCost += $this->fromToAbil($this->abilDict[$abID]['value'],$amend,  $abID);
			}
		}
		return $totalCost;		
	}   
	

	public function reset()
	{
		$uID = $this->user;
		mysql_query("DELETE FROM pg_abilita_levels WHERE pgID = $uID");
	}



	public function resetAndRestore($initialDistribution)
	{

		$me = new PG($this->user);
		$uID = $me->ID; 

		
		$this->reset(); 

		$this->superSet(
			array(
				array('IQ',$initialDistribution['IQ']),
				array('DX',$initialDistribution['DX']),
				array('HT',$initialDistribution['HT']),
				array('PE',$initialDistribution['PE']),
				array('WP',$initialDistribution['WP'])
			)
		);
		
		$msgString = 'AB-Reset: ';
		foreach($initialDistribution as $amenderK => $amenderV)
			$msgString.=' '.$amenderK.'('.$amenderV.')';

		
		//SET PUNTI
		$tpt = 700+floor( PG::getSomething($uID,"totalPoints") / 12);
		mysql_query("UPDATE pg_users SET pgUpgradePoints = $tpt WHERE pgID = $uID");


		//SET RAZZA
		$this->populate();
		$this->superImposeRace($me->pgSpecie);

		$msgString.=' '.$tpt . ' UP, razza ' . $me->pgSpecie;
		$me->addNote($msgString);
	}

	public function preload_objects()
	{
		$me = $this->ID;
		mysql_query("DELETE FROM fed_objects_ownership WHERE owner = $me AND oID IN (SELECT oID FROM fed_objects WHERE oType ='SERVICE')");
		mysql_query("INSERT INTO fed_objects_ownership(oID,owner) VALUES (1,$me),(124,$me)");
	}

	public function superImposeRace($race)
	{  


		$file_handle = fopen("data/char_progression.txt", "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);

		}
		
		$tt = array('ABI' => array(), 'CAR' => array());
		$res = mysql_query("SELECT * FROM pg_abilita_bonus WHERE 1");
		while($resA = mysql_fetch_assoc($res)){
			if (!array_key_exists($resA['species'], $tt[$resA['type']]))
				$tt[$resA['type']][$resA['species']] = array();
			
			$tt[$resA['type']][$resA['species']][] = array($resA['abID'],$resA['abMod']);
		}

		/*$t['Umana']= array(	array(38,0),array(31,1) );
		$t['Vulcaniana']=  array(array(60,2),array(59,1),array(61,1),array(56,0));
		$t['Betazoide'] = array(array(61,2),array(20,0),array(60,2));
		$t['Trill'] =array(array(21,1),array(20,1),array(9,0),array(35,2));
		$t['Andoriana'] = array(array(10,2),array(21,1),array(17,1),array(52,2),array(4,3));
		$t['Bajoriana'] = array(array(7,1),array(12,3),array(18,1),array(21,2),array(19,0));
		$t['Boliana'] = array(array(7,2),array(11,2),array(35,2),array(34,0),array(20,2), array(19,1));
		$t['Terosiana'] = array(array(42,1),array(18,1),array(8,2),array(11,2));
		$t['Caitiana'] = array(array(10,1),array(12,3),array(21,2));
		$t['Koyar'] = array(array(60,2),array(61,1),array(12,2),array(11,1),array(21,0));
		$t['Xenita'] = array(array(12,1),array(21,2),array(52,1),array(56,1));
		$t['Tellarita'] = array(array(35,2),array(13,2),array(8,2),array(22,1),array(34,2),array(21,2),array(33,1));
		$t['Zaldan'] = array(array(13,1),array(10,3),array(4,1),array(8,2),array(21,2));
		
		$r['Vulcaniana']=  array(array('WP',1),array('HT',1)); // 11 + 15 = 26 (79)
		$r['Betazoide'] = array(array('HT',-1),array('WP',2)); // -3 +11+16 = 24 (79)
		$r['Trill'] =array(array('IQ',1),array('HT',2)); // 20 + 15+19 = 54 (100)
		$r['Andoriana'] = array(array('DX',1),array('HT',1)); // 15+15 = 30 (84)
		$r['Bajoriana'] = array(array('WP',2),array('PE',1)); // 11+11 = 22 (84)
 		$r['Boliana'] = array(array('DX',-2),array('PE',2),array('WP',1)); // 11+11 = 22 (84)
 		$r['Terosiana'] = array(array('DX',2),array('WP',1),array('HT',-1)); // 11+11 = 22 (84)
 		$r['Caitiana'] = array(array('DX',1),array('PE',2),array('HT',-2)); // (85)
 		$r['Koyar'] = array(array('HT',-1),array('DX',+1),array('WP',+2));
 		$r['Xenita'] = array(array('IQ',-2),array('DX',+1),array('WP',+1),array('PE',+2));
 		$r['Tellarita'] = array(array('DX',-2),array('HT',+1));
 		$r['Zaldan'] = array(array('DX',-2),array('HT',+3),array('WP',-1));*/

		if (array_key_exists($race,$tt['ABI']))
			$this->superSet($tt['ABI'][$race]);
		
		$ediList = array();
		if (array_key_exists($race,$tt['CAR']))
		{
			foreach($tt['CAR'][$race] as $k)
			{
				$sta = $this->abilDict[$k[0]]['value'];
				$ediList[]= array($k[0],$sta+$k[1]);
			}
			$this->superSet($ediList);
		}

		if($race == "Umana"){
			$points = $this->userUpgradePoints['pgUpgradePoints'];
			PG::setSomething($this->user,'UP',$points+50);
		}
	}

	public function superSet($differential)
	{
		foreach($differential as $amender){
				$abeID = $amender[0];
				$amend = $amender[1];  
				$uID = $this->user; 

				mysql_query("DELETE FROM pg_abilita_levels WHERE pgID = $uID AND abID = '$abeID'");
				mysql_query("INSERT INTO pg_abilita_levels(pgID, abID, value) VALUES ($uID,'$abeID',$amend)");
		}
	}

	public function performVariation($differential)
	{
		$totalCost = 0;
		$points = $this->userUpgradePoints['pgUpgradePoints'];
		$neededPoints = $this->calculateVariationCost($differential);

		if($neededPoints <= $points)
		{  
			PG::setSomething($this->user,'UP',$points-$neededPoints);
			foreach($differential as $amender){
				$abeID = $amender[0];
				$amend = $amender[1];  
				$uID = $this->user;
				mysql_query("DELETE FROM pg_abilita_levels WHERE pgID = $uID AND abID = '$abeID'");
				mysql_query("INSERT INTO pg_abilita_levels(pgID, abID, value) VALUES ($uID,'$abeID',$amend)");
			}
		}
	}
	
}

?>