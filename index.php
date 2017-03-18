<?php
require "vendor/autoload.php";
require "cookpad/Cookpad.php";

$cookpad = new Cookpad(new \PHPHtmlParser\Dom);

//foreach ($cookpad->all() as $r) {
// echo $r['title'];
//}

$cookpad->set('locate', 'id');
//echo $cookpad->all();
echo $cookpad->detail('resep/1453533');

//TODO: Detail Recooks not yet.
//TODO: Search not yet.
