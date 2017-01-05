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
            $page = Page::open($file['path']);
            $u = $page->get('url');
            $t = $page->get('title');
            $html .= '<li>';
            if ($url->current === $u) {
                $html .= '<span>' . $t . '</span>';
            } else {
                $html .= '<a href="' . URL::short($u, false) . '">' . $t . '</a>';
            }
            $html .= fn_archive(Path::D($file['path']) . DS . $file['slug']);
            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}

Block::set('archive', function($content) use($url) {
    return Block::replace('archive', function() use($url) {
        $cache = CACHE . DS . 'extend.plugin.archive.php';
        $x = File::open($cache)->import(["", ""]);
        $hash = md5(json_encode(File::explore(PAGE, true, true)));
        if ($x[0] === $hash) {
            return str_replace(' href="', ' href="' . $url . '/', $x[1]);
        }
        $x = [$hash, fn_archive()];
        File::export($x)->saveTo($cache);
        return $x[1];
    }, $content);
});