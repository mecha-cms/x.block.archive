<?php

function fn_archive($path = PAGE) {
    $html = "";
    if (!Is::D($path)) {
        return $html;
    }
    global $url;
    if ($files = Get::pages($path, 'page', 1, 'slug')) {
        $html .= '<ul>';
        foreach ($files as $file) {
            $w = $file['path'];
            $x = Path::D($w);
            $y = Path::N($w);
            $z = Path::B($x);
            if ($z === $y && file_exists($x . '.page') && file_exists($x . DS . $z . '.page')) {
                continue; // ignore placeholder page …
            }
            $page = Page::open($w);
            $u = $page->get('url');
            $t = $page->get('title');
            $html .= '<li>';
            if ($url->current === $u) {
                $html .= '<span>' . $t . '</span>';
            } else {
                $html .= '<a href="' . URL::short($u, false) . '">' . $t . '</a>';
            }
            $html .= fn_archive($x . DS . $y);
            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}

Block::set('archive', function($content) use($url) {
    return Block::replace('archive', function() use($url) {
        $cache = str_replace(ROOT, CACHE, __DIR__) . '.php';
        $x = File::open($cache)->import(["", ""]);
        $hash = md5(json_encode(File::explore(PAGE, true, true)));
        if ($x[0] !== $hash) {
            $x = [$hash, fn_archive()];
            File::export($x)->saveTo($cache);
        }
        return str_replace(' href="', ' href="' . $url . '/', $x[1]);
    }, $content);
});