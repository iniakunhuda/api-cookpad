<?php

class Cookpad
{
    public $locate = 'id', $search = "/cari";

    public $url;

    private $dom, $version = 2017.3;

    public function __construct($phphtmlparser)
    {
        ini_set('display_errors', 1);
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
        if($val=="jp"){
            $data['status'] = 500;
            $data['message'] = "Sorry! Cookpad API v".$this->version." for Japan is not supported.";
            die($this->toJson($data));
        }
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
        $page   = 1,
        $limit  = 0,
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

        // When 404
        if(count($this->dom->find('body.errors')) > 0){
            $data['status']     = 404;
            $data['message']    = "Url ".$url." not found!";
            die($this->toJson($data));
        }

        $data['status']     = 200;
        $data['url']        = $url;
        $data['page']['before']   = ($page == 1) ? 1 : $page-1;
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
                (count($item->find('.icf--timer')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['author'])
                    ?  isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 0
                    : trim($item->find('li')->text)
                    : 0;
            $data['data'][$key]['portion'] =
                (count($item->find('.icf--user')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['duration']) // diff time & portion
                    ? isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 1
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
    public function detail($target = "")
    {
        if($target == "") {
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
        $data['data'][0]['author_profile'] =
            str_replace('/'.$this->locate.'/', '', $this->url).
            trim($content->find('section[class="author-container"] a')->href);
        $data['data'][0]['description'] = trim($content->find('div.recipe-show__story p')->text);
        $data['data'][0]['image'] = trim($this->dom->find('.tofu_image img')->src);
        $data['data'][0]['likes'] = (int) trim($content->find('.recipe-show__metadata span')->text);
        $data['data'][0]['duration'] = trim($content->find('div[data-field-name="cooking_time"]')->text);
        $data['data'][0]['portion'] = trim($content->find('div[data-field-name="serving"]')->text);

        $ingredients = $content->find('li.ingredient');

        foreach ($ingredients as $key => $item) {
            $data['data'][0]['ingredients'][$key]['name'] = trim(trim($item->find('span.ingredient__quantity')->text) . ' ' . trim($item->find('div.ingredient__details')->text));
        }

        $steps = $content->find('steps');

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

        // if($this->dom->find('div.document__panel ul.list-inline li')){
        //   $relateds = $this->dom->find('div.document__panel ul.list-inline li');
        //
        //   foreach ($relateds as $key => $related) {
        //       $data['related'][$key]['key'] = trim($related->find('a')->text);
        //       $data['related'][$key]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).trim($related->find('a')->href);
        //   }
        // }

        return $this->toJson($data);
    }


    /**
     * View user profile
     * @param string $target
     * @param string $searchrecipe
     * @param int $page
     * @return string
     */
    public function profile(
        $target         = "",
        $searchrecipe   = "",
        $page           = 1
    )
    {
        if($target == "") {
            $data['status']     = 500;
            $data['message']    = "Url must a valid!";
            die($this->toJson($data));
        }

        $url = $this->url.$target;
        $userurl = $url;
        ($page > 1 || $searchrecipe != "")
            ? $url .= "?page=" . $page . "&u=" . urlencode($searchrecipe)
            : "";
        $dom = $this->dom->load($url);

        // When 404
        if(count($dom->find('body.errors')) > 0){
            $data['status']     = 404;
            $data['message']    = "Url ".$url." not found!";
            die($this->toJson($data));
        }

        $content = $dom->find('.main-container');
        $usercontent = $this->dom->load($userurl);
        $data['status']     = 200;
        if($searchrecipe != "" && $searchrecipe != "*") {
            $data['keyword'] = urlencode($searchrecipe);
        }
        $data['url'] = $url;

        $data['page']['before']   =
            (count($content->find('span[class="page"] a[rel="prev"]')) > 0)
                ? (int) $content->find('span[class="page"] a[rel="prev"]')->text
                : 1;
        $data['page']['now']    =
            (count($content->find('span[class="page current"]')) > 0)
            ? (int) $content->find('span[class="page current"]')->text
            : 1;
        $data['page']['next']   =
            (count($content->find('span[class="page"] a[rel="next"]')) > 0)
                ? (int) $content->find('span[class="page"] a[rel="next"]')->text
                : $data['page']['now'];

        $data['profile'][0]['name'] = trim($content->find('h1[class="user-header__name"] a')->text);
        $data['profile'][0]['avatar'] = trim($content->find('.user-header__avatar')->src);
        $data['profile'][0]['banner'] = str_replace('\');', '', str_replace('background-image: url(\'', '', trim($content->find('.user-background__image')->style)));
        $data['profile'][0]['description'] =
            (count($usercontent->find('.user-header__profile')) > 0)
                ? trim($usercontent->find('.user-header__profile')->text)
                : "";
        $data['profile'][0]['city'] =
            (count($usercontent->find('div[class="user-header__location subtle"]')) > 0)
                ? trim($usercontent->find('div[class="user-header__location subtle"]')->text)
                : "";
        $data['profile'][0]['recipes'] = intval(str_replace('.','', $content->find('.tab-list__count')[0]->text));
        $data['profile'][0]['photos'] = intval(str_replace('.','', $content->find('.tab-list__count')[1]->text));
        $data['profile'][0]['comments'] = intval(str_replace('.','', $content->find('.tab-list__count')[2]->text));
        $data['profile'][0]['following'] =
            (count($usercontent->find('div[class="user-header__follows-count"] a span')[0]) > 0)
                ? intval(str_replace('.', '', $usercontent->find('div[class="user-header__follows-count"] a span')[0]->text))
                : 0;
        $data['profile'][0]['followers'] =
            (count($usercontent->find('div[class="user-header__follows-count"] a span')[1]) > 0)
                ? intval(str_replace('.', '', $usercontent->find('div[class="user-header__follows-count"] a span')[1]->text))
                : 0;

        foreach($content->find('li.recipe') as $key => $item) {
            $itemurl = explode('/', $item->find('a')->href);
            $data['data'][$key]['id'] = (int)explode('-', $itemurl[3])[0]; // ex: 12121-bawang-merah (12121)
            $data['data'][$key]['title'] = trim($item->find('.recipe-title span')->text);
            $data['data'][$key]['url'] = $this->url . $itemurl[2] . '/' . $itemurl[3]; // ex: [url]/[locate]/url
            $data['data'][$key]['image'] = trim($item->find('img')->src);
            $data['data'][$key]['author'] = trim($item->find('.subtle')->text);
            $data['data'][$key]['author_avatar'] = trim($item->find('img.avatar')->src);
            $data['data'][$key]['description'] = trim($item->find('.recipe__ingredients')->text);
            $data['data'][$key]['duration'] =
                (count($item->find('.icf--timer')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['author'])
                    ?  isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 0
                    : trim($item->find('li')->text)
                    : 0;
            $data['data'][$key]['portion'] =
                (count($item->find('.icf--user')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['duration']) // diff time & portion
                        ? isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 1
                        : trim($item->find('li')->text)
                    : 1;
        }

        if(count($content->find('li.recipe')) < 1) {
            $data['status'] = 404;
            $data['message'] = "data recipes not found!";
            $data['data'] = [];
        }

        return $this->toJson($data);
    }


    /**
     * View Recook
     * @param string $target
     * @return string
     */
    public function recook($target = "")
    {
        if($target == "") {
            $data['status']     = 500;
            $data['message']    = "Url must a valid!";
            die($this->toJson($data));
        }

        $url = $this->url.$target;
        $dom = $this->dom->load($url);

        // When 404
        if(count($dom->find('body.errors')) > 0){
            $data['status']     = 404;
            $data['message']    = "Url ".$url." not found!";
            die($this->toJson($data));
        }
        $content = $dom->find('.main-container');
        $data['status'] = 200;
        $data['url'] = $url;

        $data['data'][0]['title'] = str_replace('/'.$this->locate.'/', '', $this->url).$content->find('a[class="recipe-title strong small"]')->text;
        $data['data'][0]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).$content->find('a[class="link-unstyled"]')->href;
        $data['data'][0]['image'] = $content->find('.media__img img')->src;
        $data['data'][0]['author'] = str_replace('/'.$this->locate.'/', '', $this->url).$content->find('a[class="link-unstyled"]')->text;
        $contenttime = $dom->find('.recipe__metadata');
        $data['data'][0]['duration'] =
            (count($contenttime->find('.icf--timer')) > 0)
                ? (trim($contenttime->find('li')->text) == $data['data'][0]['author'])
                    ?  isset($contenttime->find('li')[1])
                        ? trim($contenttime->find('li')[1]->text) : 0
                    : trim($contenttime->find('li')->text)
                : 0;
        $data['data'][0]['portion'] =
            (count($contenttime->find('.icf--user')) > 0)
                ? (trim($contenttime->find('li')->text) == $data['data'][0]['duration']) // diff time & portion
                    ? isset($contenttime->find('li')[1])
                        ? trim($contenttime->find('li')[1]->text) : 1
                    : trim($contenttime->find('li')->text)
                : 1;

        $data['recooks'][0]['name'] = trim($content->find('div[class="split-header__main"] span a')->text);
        $data['recooks'][0]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).$content->find('div[class="split-header__main"] span a')->href;
        $data['recooks'][0]['image'] = trim($content->find('#comment_image')->src);
        $data['recooks'][0]['date'] = trim($content->find('div[class="split-header__secondary small subtle"] time')->text);
        $data['recooks'][0]['message'] = trim($content->find('div[data-state="viewing"]')->text);
        $data['recooks'][0]['likes'] = (int) trim($content->find('li[class="likes-count"]')->text);

        if(isset($content->find('div[class="comment-reply media"]')[0])){
            foreach($content->find('div[class="comment-reply media"]') as $index => $reply) {
                $data['recooks'][0]['comments'][$index]['name'] = trim($reply->find('div[class="media__body comment-reply__body"] div a')->text);
                $data['recooks'][0]['comments'][$index]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).$reply->find('div[class="media__body comment-reply__body"] div a')->href;
                $data['recooks'][0]['comments'][$index]['image'] = $reply->find('div[class="comment-reply__img media__img"] img')->src;
                $data['recooks'][0]['comments'][$index]['message'] = trim($reply->find('div[data-state="viewing"]')->text);
                $data['recooks'][0]['comments'][$index]['likes'] = (int) trim($reply->find('li[class="likes-count"]')->text);
            }
        }

        return $this->toJson($data);
    }


    /**
     * Search of Recipes
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @param bool $random
     * @return string
     */
    public function search(
        $keyword    = "",
        $page       = 1,
        $limit      = 0,
        $random     = false
    )
    {
        if(is_null($keyword)) {
            $data['status'] = 500;
            $data['message'] = "Search must with valid keywords!";
            die($this->toJson($data));
        }

        if(is_int($keyword)) {
            $data['status'] = 500;
            $data['message'] = "Search must string, not number!";
            die($this->toJson($data));
        }

        if(is_null($this->search)) {
            $data['status'] = 500;
            $data['message'] = "Search page must defined before. Example: new Cookpad->set('search', '/cari')";
            die($this->toJson($data));
        }

        if(explode('/', $this->search)[0]) {
            $data['status'] = 500;
            $data['message'] = "Search page must have / (symbol) at first letter. Example: /search, /cari";
            die($this->toJson($data));
        }

//        if(count(explode('/', $this->search)[1]) > 0) {
//            $data['status'] = 500;
//            $data['message'] = "Error! Keywords search dont containt / (symbol)";
//            die($this->toJson($data));
//        }

        if(!is_int($page)) {
            $data['status']     = 500;
            $data['message']    = "Page must number!";
            die($this->toJson($data));
        }

        $url = $this->url . str_replace('/', '', $this->search) . '/' . urlencode($keyword);
        ($page > 1) ? $url .= "?page=" . $page : "";
        $items = $this->dom->load($url);

        // When 404
        if(count($this->dom->find('body.errors')) > 0){
            $data['status']     = 404;
            $data['message']    = "Url ".$url." not found!";
            die($this->toJson($data));
        }

        // Not Found
        if(count($items->find('.blank-slate__icon')) > 0) {
            $data['status'] = 404;
            $data['message'] = "Search with keywords '" . $keyword . "'";
            if($page > 0) {
                $data['message'] .= " at page " . $page  . " not found!";
            }
            $suggests = $items->find('ul[class="list-inline small"] li');
            foreach ($suggests as $key => $suggest) {
                $data['suggestion'][$key]['name'] = $suggest->find('a')->text;
                $data['suggestion'][$key]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).$suggest->find('a')->href;
            }
            die($this->toJson($data));
        }

        $pagination = $this->dom->find('.pagination');
        $pagination_href = explode('?', $pagination->find('a')->getAttribute('href')); // ex: /id?page=2

        $data['status']     = 200;
        $data['keyword']     = $keyword;
        $data['url']        = $url;
        $data['page']['before']   =
        (count($this->dom->find('span[class="page"] a[rel="prev"]')) > 0)
            ? (int) $this->dom->find('span[class="page"] a[rel="prev"]')->text
            : 1;
        $data['page']['now']    = (int) $this->dom->find('span[class="pagination__page page current"]')->text;
        $data['page']['next']   =
            (count($this->dom->find('span[class="page"] a[rel="next"]')) > 0)
                ? (int) $this->dom->find('span[class="page"] a[rel="next"]')->text
                : $data['page']['now'];
        $data['total']      = count($this->dom->find('li.recipe'));
        $data['total_all']      = intval(str_replace('.','', $this->dom->find('span[class="results-header__count subtle"]')->text));
        ($random) ? $data['random'] = true : "";

        if(count($items->find('div[class="results-header__suggestion"]')) > 0) {
            $data['typo'][0]['name'] = $items->find('.results-header__suggestion a')->text;
            $data['typo'][0]['url'] = str_replace('/'.$this->locate.'/', '', $this->url).$items->find('.results-header__suggestion a')->href;
        }

        foreach($items->find('li[class="wide-card ranked-list__item"]') as $key => $item){
            $itemurl = explode('/', $item->find('a')->href);
            $data['data'][$key]['id'] = (int) explode('-', $itemurl[3])[0]; // ex: 12121-bawang-merah (12121)
            $data['data'][$key]['title'] = trim($item->find('.recipe-title span')->text);
            $data['data'][$key]['url'] = $this->url.$itemurl[2].'/'.$itemurl[3]; // ex: [url]/[locate]/url
            $data['data'][$key]['image'] = trim($item->find('img')->src);
            $data['data'][$key]['author'] = trim($item->find('.subtle')->text);
            $data['data'][$key]['author_avatar'] = trim($item->find('img.avatar')->src);
            $data['data'][$key]['description'] = trim($item->find('.wide-card__body')->text);
            $data['data'][$key]['duration'] =
                (count($item->find('.icf--timer')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['author'])
                    ?  isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 0
                    : trim($item->find('li')->text)
                    : 0;
            $data['data'][$key]['portion'] =
                (count($item->find('.icf--user')) > 0)
                    ? (trim($item->find('li')->text) == $data['data'][$key]['duration']) // diff time & portion
                    ? isset($item->find('li')[1]) ? trim($item->find('li')[1]->text) : 1
                    : trim($item->find('li')->text)
                    : 1;

            foreach ($this->dom->find('ul[class="list-inline small"] li a') as $key => $related) {
                $data['related'][$key]['name'] = str_replace('</b>', '', str_replace('<b class="lighter">', '', $related->innerHtml));
                $data['related'][$key]['url'] = str_replace('?event=search.related_search', '', str_replace('/'.$this->locate.'/', '', $this->url).$related->href);
            }

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
     * Method alternate for call all(), detail(), search()
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @param bool $random
     * @return string
     */
    public function get(
        $keyword    = "",
        $page       = 1,
        $limit      = 0,
        $random     = false
    )
    {
        $url = explode('/', $keyword);
        $array_profile_url = array('users', 'pengguna', 'perfil', 'nguoi-su-dung', 'profil', 'مستخدمين', 'usuarios', '使用者', '사용자', 'utenti', 'كاربر', 'felhasznalok', 'brugere', 'kitchen');
        $array_recook_url = array('recook');

        if($keyword == "*") {
            return $this->all($page, $limit, $random);
        }
        elseif(!is_null($keyword) && in_array($url[0], $array_profile_url) || in_array($url[1], $array_profile_url)) {
            $limit = ($limit == 0) ? 1 : 0;
            $page = ($page == 1) ? "*" : $page;
            return $this->profile($keyword, $page, $limit); // (target, searchrecipe, page)
        }
        elseif(!is_null($keyword) && in_array($url[0], $array_recook_url) || in_array($url[1], $array_recook_url)) {
            return $this->recook($keyword); // (target)
        }
        elseif(!is_null($keyword) && $url[1]) {
            return $this->detail($keyword);
        }
        else {
            return $this->search($keyword, $page, $limit, $random);
        }
    }
}
