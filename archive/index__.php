<?php

function fn_archive($path = PAGE) {
    $html = "";
    if (!Is::D($path)) {
        return $html;
    }
    global $url;
    if ($files = Get::pages($path, 'page', [1, 'slug'], 'path')) {
        $html .= '<ul>';
        foreach ($files as $file) {
            $x = Path::D($file);
            $y = Path::N($file);
            $z = Path::B($x);
            if ($z === $y && file_exists($x . '.page') && file_exists($x . DS . $z . '.page')) {
                continue; // ignore placeholder page…
            }
            $page = new Page($file);
            $u = $page->url;
            $t = $page->title;
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

function fn_archive_replace_archive($content) {
    global $url;
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
}

Block::set('archive', 'fn_archive_replace_archive', 10);