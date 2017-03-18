<?php

class Cookpad
{
    public $locate = 'id';

    public $url;

    private $dom;

    public function __construct($phphtmlparser)
    {
        $this->dom = $phphtmlparser;
        $this->url = 'https://cookpad.com/'.$this->locate.'/';
    }

    private function toJson($data)
    {
        header('Content-Type: application/json');
        return json_encode($data);
    }


    /**
     * Set local variable
     * @param $var
     * @param $val
     * @return array
     */
    public function set($var, $val)
    {
        $data = array();
        if($this->$var) {
            if($this->$var = $val) {
                $data['status']     = 200;
                $data['message']    = "Success! Set local variable.";
                $this->url = 'https://cookpad.com/'.$this->locate.'/';
            } else {
                $data['status']     = 500;
                $data['message']    = "Failed! Local variable not set!";
                die($this->toJson($data));
            }
        } else {
            $data['status']     = 404;
            $data['message']    = "Failed! Local variable not found!";
            die($this->toJson($data));
        }
        return $data;
    }


    /**
     * Get all recipes from Homepage
     * @param int $page
     * @param int $limit
     * @param bool $random
     * @return string
     */
    public function all(
        $page = 1,
        $limit = 0,
        $random = false
    )
    {
        if(!is_int($page)) {
            $data['status']     = 500;
            $data['message']    = "Page must number!";
            die($this->toJson($data));
        }
        $url = ($page == 1) ? $this->url : $this->url.'?page='.$page;

        $this->dom->load($url);
        $data       = array();
        $items =
            (count($this->dom->find('.masonry__item')) > 0)
            ? $this->dom->find('.masonry__item')
            : $this->dom->find('.recipe');
        $pagination = $this->dom->find('.pagination');
        $pagination_href = explode('?', $pagination->find('a')->getAttribute('href')); // ex: /id?page=2

        $data['status']     = 200;
        $data['url']        = $url;
        $data['page']['last']   = ($page == 1) ? 1 : $page-1;
        $data['page']['now']    = ($page == 1) ? 1 : $page;
        $data['page']['next']   = (int) explode('=', $pagination_href[1])[1]; // ex: ?page=2 -> 2
        $data['total']      = count($items);
        ($random) ? $data['random'] = true : "";

        foreach($items as $key => $item){
            $itemurl = explode('/', $item->find('a')->href);
            $data['data'][$key]['id'] = (int) explode('-', $itemurl[3])[0]; // ex: 12121-bawang-merah (12121)
            $data['data'][$key]['title'] =
                (count($item->find('.recipe-title span')) > 0)
                    ? trim($item->find('.recipe-title span')->text) // not mansory
                    : trim($item->find('.recipe-title')->text); // with mansory
            $data['data'][$key]['url'] = $this->url.$itemurl[2].'/'.$itemurl[3]; // ex: [url]/[locate]/url
            $data['data'][$key]['image'] =
                (count($item->find('.recipe__photo')) > 0)
                    ? trim($item->find('.recipe__photo')->src) // with mansory
                    : trim($item->find('img')->src);
            $data['data'][$key]['author'] =
                (count($item->find('.feed__meta li')) > 0)
                    ? trim($item->find('.feed__meta li')->text) // with mansory
                    : trim($item->find('.subtle')->text);
            $data['data'][$key]['description'] =
                (count($item->find('.recipe__ingredients')) > 0)
                    ? trim($item->find('.recipe__ingredients')->text) // not mansory
                    : "";
            $data['data'][$key]['duration'] =
                (count($item->find('.icf--timer')) > 0) ? trim($item->find('li')->text) : 0;
            $data['data'][$key]['portion'] =
                (count($item->find('.icf--user')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['duration']) // diff time & portion
                        ? trim($item->find('li')[1]->text)
                        : trim($item->find('li')->text)
                    : 1;

            // Limit
            if($limit > 0) {
                if($limit == $key+1) {
                    break;
                }
            }
        }

        // Random
        if($random) {
            shuffle($data['data']);
        }

        return $this->toJson($data);
    }


    /**
     * View detail of Recipe
     * @param string $target <url>
     * @return string
     */
    public function detail(
        $target = ""
    )
    {
        if(is_null($target)) {
            $data['status']     = 500;
            $data['message']    = "Url must a valid!";
            die($this->toJson($data));
        }

        $url = $this->url.$target;
        $this->dom->load($url);

        // When 404
        if(count($this->dom->find('body.errors')) > 0){
            $data['status']     = 404;
            $data['message']    = "Url ".$url." not found!";
            die($this->toJson($data));
        }

        $content = $this->dom->find('.editor');

        $data['status']     = 200;
        $data['url'] = trim($this->dom->find('link[itemprop="url"]')->href);

        $data['data'][0]['title'] = trim($content->find('h1.recipe-show__title')->text);
        $data['data'][0]['author'] = trim($content->find('span[itemprop="author"]')->text);
        $data['data'][0]['author_avatar'] = trim($content->find('img.avatar')->src);
        $data['data'][0]['description'] = trim($content->find('div.recipe-show__story p')->text);
        $data['data'][0]['image'] = trim($this->dom->find('.tofu_image img')->src);
        $data['data'][0]['likes'] = (int) trim($content->find('.recipe-show__metadata span')->text);
        $data['data'][0]['duration'] = trim($content->find('div[data-field-name="cooking_time"]')->text);
        $data['data'][0]['portion'] = trim($content->find('div[data-field-name="serving"]')->text);

        $ingredients = $content->find('li.ingredient');

        foreach ($ingredients as $key => $item) {
            $data['data'][0]['ingredients'][$key]['name'] = trim(trim($item->find('span.ingredient__quantity')->text) . ' ' . trim($item->find('div.ingredient__details')->text));
        }

        $steps = $content->find('li.step');

        foreach ($steps as $key => $step) {
            $data['data'][0]['steps'][$key]['name'] = trim($step->find('p.step__text')->text);
            if(count($step->find('div.step-image img')) > 0) {
                if(count($step->find('div.step-image div.tofu_image')) > 0) {
                    $data['data'][0]['steps'][$key]['pict'] = trim($step->find('div.step-image div.tofu_image img')->src);
                } else {
                    $data['data'][0]['steps'][$key]['pict'] = "";
                }
            }
        }

        $recooks = $this->dom->find('a.side-swipe__item');

        foreach ($recooks as $key => $recook) {
            $data['data'][0]['recooks'][$key]['id'] = $this->url.explode('/', trim($recook->href))[2].'/'.explode('/', trim($recook->href))[3];
            $data['data'][0]['recooks'][$key]['name'] = trim($recook->find('div.recipe-title')->text);
            $data['data'][0]['recooks'][$key]['avatar'] = trim($recook->find('img.avatar')->src);
            $data['data'][0]['recooks'][$key]['message'] = trim($recook->find('div.subtle')->text);
            $data['data'][0]['recooks'][$key]['pict'] = trim($recook->find('img.side-swipe__image')->src);
        }

        $relateds = $this->dom->find('div.document__panel ul.list-inline li');

        foreach ($relateds as $key => $related) {
            $data['related'][$key]['key'] = trim($related->find('a')->text);
            $data['related'][$key]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).trim($related->find('a')->href);
        }

        return $this->toJson($data);
    }

}