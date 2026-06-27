<?php
exec('php -l api.php 2>&1', $out, $ret);
echo implode("\n", $out);
