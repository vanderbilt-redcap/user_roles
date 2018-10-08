<?php
$header = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "header.html");
$header = str_replace("CSS_PATH-", APP_PATH_CSS, $header);
$header = str_replace("JS_PATH-", APP_PATH_JS, $header);
$header = str_replace("MODULE_JS_FILE", $module->getUrl('js/userRoles.js'), $header);
echo $header;

echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "tableA.html");

echo file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "html" . DIRECTORY_SEPARATOR . "footer.html");