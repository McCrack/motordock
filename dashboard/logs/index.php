<?php

$handle = fopen("test.txt", "a");
fwrite($handle, "<---------- [".date("H:i:s")."] ---------->\n");
fclose($handle);


?>