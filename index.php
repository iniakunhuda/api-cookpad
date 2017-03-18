<?php
require "vendor/autoload.php";
require "cookpad/Cookpad.php";

$cookpad = new Cookpad(new \PHPHtmlParser\Dom);

//foreach ($cookpad->all() as $r) {
// echo $r['title'];
//}

$cookpad->set('locate', 'id');
echo $cookpad->all();

//var_dump($cookpad->tes()->innerHtml());
