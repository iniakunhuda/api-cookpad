# Cookpad API
Unofficial Cookpad API for developer

## Description
Cookpad API is grab or scrap data from cookpad.com website with PHP. 
You can find all feature from cookpad with simply use. Features list :   
- All Recipes
- Detail Recipe
- Detail User
- Detail Recook
- Search Recipes

## Install
1. Clone / download this repository.
2. Open terminal. Go to your project directory then run,
```php
composer install
```
3. Open your url directory on browser.

## How to use ?

1. Open index.php file (your root file).
2. Load composer and Cookpad class,
```php
require "vendor/autoload.php";
require "cookpad/Cookpad.php";
```
3. Declaration new Cookpad,
```php
$cookpad = new Cookpad(new \PHPHtmlParser\Dom);
```
Because we use PHPHtmlParser to scrap website, you must initializate too.
 
## Methods

### Set Locate 
```php
$cookpad->set('locate', $locate);
```
Cookpad API set country. List all locate you can find at <a href="cookpad.com/en/regions" target="_blank">cookpad.com/en/regions</a>. By default, locate set <b>id</b> (Indonesia). In version 2017.3 Cookpad API not supported with <b>jp</b> (Japan). 


### Set Search URL 
```php
$cookpad->set('url', $urlwithslash);
```
By default, search URL set <b>/search</b>.


### Get All Recipes
```php
$cookpad->all($page, $limit, $random = false);
```
Example :
```php
$cookpad->all();
```
Response :
```json
{
    "status": 200,
    "url": "https://cookpad.com/id/",
    "page": {
        "before": 1,
        "now": 1,
        "next": 2
    },
    "total": 21,
    "data": [
        {
            "id": 1408010,
            "title": "Sayur Bayam Jagung Manis",
            "url": "https://cookpad.com/id/resep/1408010-sayur-bayam-jagung-manis",
            "image": "https://img-global.cpcdn.com/003_recipes/8079f3d1d2a4a803/400sq70/photo.jpg",
            "author": "oleh Adam&#39;s Mommy",
            "description": "",
            "duration": "15 menit",
            "portion": "2 piring"
        }
    ]
}
```

### Detail Recipe
```php
$cookpad->detail($target);
```
Example :
```php
$cookpad->detail('resep/1408010-sayur-bayam-jagung-manis');
```
or
```php
$cookpad->detail('resep/1408010');
```
<strong>IMPORTANT!</strong> You can't set target with cookpad url, example http://cookpad.com/id/resep/1408010-sayur-bayam-jagung-manis.

Response :
```json
{

    "status": 200,
    "url": "https://cookpad.com/id/resep/1408010-sayur-bayam-jagung-manis",
    "data": [
        {
            "title": "Sayur Bayam Jagung Manis",
            "author": "Adam&#39;s Mommy",
            "author_avatar": "https://img-global.cpcdn.com/003_users/1921ada0286d29c3/56x56cq50/photo.jpg",
            "author_profile": "https://cookpad.com/id/pengguna/5899742",
            "description": "",
            "image": "https://img-global.cpcdn.com/003_recipes/8079f3d1d2a4a803/664x470cq70/photo.jpg",
            "likes": 4,
            "duration": "15 menit",
            "portion": "2 porsi",
            "ingredients": [
                {
                    "name": "1 ikat bayam"
                }
            ],
            "steps": [
                {
                    "name": "Potong-potong bayam dan jagung lalu bersihkan",
                    "pict": ""
                }
            ],
            "recooks": [
                {
                    "id": "https://cookpad.com/id/recook/1527234",
                    "name": "Safriani (Shafira&#39;s)",
                    "avatar": "https://img-global.cpcdn.com/003_users/7d34f4453764e85e/50x50cq50/photo.jpg",
                    "message": "Makasih resep_nya mommy...",
                    "pict": "https://img-global.cpcdn.com/003_photo_reports/8724421b7688fcb8/200x200cq70/photo.jpg"
                }
            ]
        }
    ],
    "related": [
        {
            "key": "sayur bayam jagung manis",
            "url": "https://cookpad.com/id/cari/sayur%20bayam%20jagung%20manis"
        },
        {
            "key": "sayur",
            "url": "https://cookpad.com/id/cari/sayur"
        }
    ]
}
```


### Detail User
```php
$cookpad->profile($target, $searchrecipe, $page);
```
Example :
```php
$cookpad->profile('pengguna/4855359', 'pizza');
```
Response :
```json
{

    "status": 200,
    "url": "https://cookpad.com/id/pengguna/4855359",
    "page": {
        "before": 1,
        "now": 1,
        "next": 1
    },
    "profile": [
        {
            "name": "Chef Fien",
            "avatar": "https://img-global.cpcdn.com/003_users/eb4a5f73636dea98/200x200cq50/photo.jpg",
            "banner": "https://img-global.cpcdn.com/003_recipes/475e61bdc971055b/800x565cq70/photo.jpg",
            "description": "Ibu rumah tangga dengan tiga anak gembul...",
            "city": "",
            "recipes": 13,
            "photos": 4,
            "comments": 6,
            "following": 1,
            "followers": 20
        }
    ],
    "data": [
        {
            "id": 2275751,
            "title": "Telor Dadar Krispii so simple",
            "url": "https://cookpad.com/id/resep/2275751-telor-dadar-krispii-so-simple",
            "image": "https://img-global.cpcdn.com/003_recipes/475e61bdc971055b/260x366cq50/photo.jpg",
            "author": "Chef Fien",
            "author_avatar": "https://img-global.cpcdn.com/003_users/eb4a5f73636dea98/64x64cq50/photo.jpg",
            "description": "telor, tepung bumbu, garam, air, minyak goreng utk menggoreng",
            "duration": 0,
            "portion": 1
        }
    ]

}
```


### Detail Recook
```php
$cookpad->recook($target);
```
Example :
```php
$cookpad->recook('recook/1527234');
```
Response :
```json
{

    "status": 200,
    "url": "https://cookpad.com/id/recook/1527234",
    "data": [
        {
            "title": "Sayur Bayam Jagung Manis",
            "url": "https://cookpad.com/id/resep/1408010-sayur-bayam-jagung-manis",
            "image": "https://img-global.cpcdn.com/003_users/7d34f4453764e85e/96x96cq50/photo.jpg",
            "author": "oleh Adam&#39;s Mommy",
            "duration": "15 menit",
            "portion": "2 porsi"
        }
    ],
    "recooks": [
        {
            "name": "Safriani (Shafira&#39;s)",
            "url": "https://cookpad.com/id/pengguna/4527062",
            "image": "https://img-global.cpcdn.com/003_photo_reports/8724421b7688fcb8/420x420cq70/photo.jpg",
            "date": "4 Maret 2017",
            "message": "Makasih resep_nya mommy...",
            "likes": 3,
            "comments": [
                {
                    "name": "Adam&#39;s Mommy",
                    "url": "https://cookpad.com/id/pengguna/5899742",
                    "image": "https://img-global.cpcdn.com/003_users/1921ada0286d29c3/60x60cq50/photo.jpg",
                    "message": "Sama sama bunda.. Makasih udah di recook.. Semoga sukaa.. 😘😉",
                    "likes": 0
                }
            ]
        }
    ]

}
```


### Search Recipes
```php
$cookpad->search($keyword, $page, $limit, $random = false);
```
Example :
```php
$cookpad->search('kukus surabaya', 1, 1);
```
Response :
```json
{
    "status": 200,
    "keyword": "kukus surabaya",
    "url": "https://cookpad.com/id/cari/kukus+surabaya",
    "page": {
        "before": 1,
        "now": 1,
        "next": 2
    },
    "total": 20,
    "total_all": 28,
    "data": [
        {
            "id": 956210,
            "title": "Bolu kukus surabaya ala saya😀",
            "url": "https://cookpad.com/id/resep/956210-bolu-kukus-surabaya-ala-saya%F0%9F%98%80",
            "image": "https://img-global.cpcdn.com/003_recipes/38533e144ae39394/260x366cq50/photo.jpg",
            "author": "Shella Rachma",
            "author_avatar": "https://img-global.cpcdn.com/003_users/3a1e4e8be7cea55a/64x64cq50/photo.jpg",
            "description": "telur, gula, tepung, sp, baking powder, Coklat bubuk, vanili kecil, margarin (cairkan tunggu ampe dingin ya jangan di masukin klo panas tar bantet)",
            "duration": 0,
            "portion": 1
        }
    ],
    "related": [
        {
            "name": "kukus",
            "url": "https://cookpad.com/id/cari/kukus"
        },
        {
            "name": "surabaya",
            "url": "https://cookpad.com/id/cari/surabaya"
        }
    ]

}
```


### All method in one word! :smile:
```php
$cookpad->get($same_with_params_methods_at_top);
```


## Features Note
+ More simply params all methods
+ Auth with Cookpad API
+ Log message and count data request.
+ thinking..

### Thanks! :heart: