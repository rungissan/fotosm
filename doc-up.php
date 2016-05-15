<?php
require("vendor/autoload.php");
$swagger = \Swagger\scan('C:\wamp\www\fotosmart.pro\api');
header('Content-Type: application/json');
echo $swagger;