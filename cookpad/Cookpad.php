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
            $data['status']     = 500;
            $data['message']    = "Failed! Local variable not found!";
            die($this->toJson($data));
        }
        return $data;
    }

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

        $data['url']        = $url;
        $data['page']['last']   = ($page == 1) ? 1 : $page-1;
        $data['page']['now']    = ($page == 1) ? 1 : $page;
        $data['page']['next']   = (int) explode('=', $pagination_href[1])[1]; // ex: ?page=2 -> 2
        $data['total']      = count($items);
        ($random) ? $data['random'] = true : "";
//
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
//
//            // Limit
//            if($limit > 0) {
//                if($limit == $key+1) {
//                    break;
//                }
//            }
        }
//
//        // Random
//        if($random) {
//            shuffle($data['data']);
//        }

        return $this->toJson($data);
    }

}