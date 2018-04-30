<?php

session_start();
include('includes/app_include.php');

$aa= mysql_query("SELECT * FROM pg_ranks WHERE aggregation IN ('Flotta Stellare','Flotta Stellare - Gradi Provvisori') ORDER BY aggregation ASC, prio DESC");
echo '<body style="color:white; background:black;"><table>';
while ($as = mysql_fetch_array($aa))
{
	
	echo '<tr><td>'.$as["Note"].'</td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["ordinaryUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["dressUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["tacticalUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["polarUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["cappottoUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["desertUniform"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["ordinaryUniformNoJackect"].'.png\' /></td>
	<td><img src=\'TEMPLATES/img/ranks/'.$as["camiceUniform"].'.png\' /></td>
	</tr>';
}
echo "</table></body>";

?>