<?php
	
function melialDataRecord(){

	$extraArray=array();
	$minimax=array();
	$extra=mysql_query("SELECT pgAvatarSquare,pg_users.pgID as pgID,pgUser,pgSezione,pg_extra_values.value FROM pg_users, pg_extra_values WHERE pg_users.pgID = pg_extra_values.pgID AND pg_extra_values.key='Melial_Points' GROUP BY pgAvatarSquare,pgID,pgUser,pg_extra_values.value ORDER BY value DESC ");
	while($extraU = mysql_fetch_assoc($extra))
	{
		$extraU['pgSezione'] = str_replace(' ','_',$extraU['pgSezione']);
		$minimax[] = $extraU['value'];
		$extraArray[]=$extraU;
	}

	$vmin=min($minimax);
	$vmax=max($minimax);
	foreach($extraArray as $k=>$v)
	{
		$extraArray[$k]['nvalue'] = floor(($v['value'] - $vmin) / ($vmax-$vmin) * (300-25)+25);
	}
	return $extraArray;
}
?>