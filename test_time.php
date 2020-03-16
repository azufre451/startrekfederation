<?php
echo "This is PHP: ". phpversion() . "<br />";

echo "Current time zone: ". date_default_timezone_get() . "<br />";

echo "Current time: ".time() . "<br />";
echo "Current date: ".date('d/m/Y H:i:s') . "<br />";
echo "Setting Timezone..." . "<br />";


date_default_timezone_set('Europe/Rome');
echo "Current time zone: ". date_default_timezone_get() . "<br />";
echo "Current time: ".time() . "<br />";
echo "Current date: ".date('d/m/Y H:i:s') . "<br />";

$ada = fopen("test_time_report.txt", "a");
fwrite($ada,time().' '.date('d/m/Y H:i:s').' '.date_default_timezone_get()."\r\n");
?>