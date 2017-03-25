# Cookpad API
Unofficial Cookpad API for developer

## Description
Cookpad API is grab or scrap data from cookpad.com website with PHP. 
You can find all feature from cookpad with simply use. Features list :   
- All Recipes
- All Recooks
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

#### Search All Recipes
```php
$cookpad->all($page, $limit, $random);
```
Response :
```json
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
```