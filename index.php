<?php
require "vendor/autoload.php";
require "cookpad/Cookpad.php";

$cookpad = new Cookpad(new \PHPHtmlParser\Dom);

//foreach ($cookpad->all() as $r) {
// echo $r['title'];
//}

$cookpad->set('locate', 'jp');

echo $cookpad->get('kitchen/5117999');
//echo $cookpad->search('kukus');
//echo $cookpad->profile('pengguna/4855359'); //no paging
//echo $cookpad->get('resep/1408010');
//echo $cookpad->detail('resep/1453533');

//TODO: Detail Recooks not yet.
//TODO: Detail Profile not yet
//TODO: Search & Detail Profile not same content

/*TODO: IDE!!

    - AI untuk mendeteksi profesi seseorang dari STATUS FACEBOOK / IDENTITAS
    - Note yang seperti litewrite.net, bisa offline & online dan bisa dibuka di HP maupun Laptop
    - Log untuk mendapatkan pencarian makanan terbanyak melalui API Cookpadcom

*/
