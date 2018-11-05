<?php
$txt = print_r($_POST, true);
$txt2 = print_r($_REQUEST, true);
fwrite($module->log, "\npost:\n$txt\n\n");
fwrite($module->log, "request:\n$txt2\n\n");