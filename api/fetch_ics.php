<?php
$url = "https://calendar.google.com/calendar/ical/id.indonesian%23holiday%40group.v.calendar.google.com/public/basic.ics";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$icsData = curl_exec($ch);
curl_close($ch);

file_put_contents('/home/fadly/work/sync-work/api/basic.ics', $icsData);
echo "Done";
