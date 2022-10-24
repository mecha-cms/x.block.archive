<?php

namespace x {
    function block__archive(string $path, $time, $i, $deep) {
        $out = "";
        if (!\is_dir($path)) {
            return $out;
        }
        $pages = \Pages::from($path)->sort($time ? [-1, 'time'] : [1, 'title']);
        if ($pages->count()) {
            $out .= '<ul>';
            foreach ($pages as $page) {
                $url = $page->url;
                $title = $page->title;
                $out .= '<li>';
                if ($time) {
                    $out .= '<time datetime="' . $page->time->ISO8601 . '">' . $page->time($time) . '</time>&#x2003;';
                }
                $out .= '<a href="' . \URL::short($url, false) . '">' . $title . '</a>';
                if ($i < $deep) {
                    $out .= content(\Path::F($page->path), $time, $i + 1, $deep);
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }
}

namespace x\block__archive {
    function block($content, $attr) {
        extract(\array_replace([
            'deep' => 4,
            'path' => "",
            'time' => false
        ], $attr), \EXTR_SKIP);
        // Refresh cache by appending `?cache=0` or `?cache=false` to the current URL
        $expire = \Request::is('Get', 'cache') && !\Get::get('cache') ? 0 : '1 year';
        $path = \rtrim(\LOT . \DS . 'page' . \strtr($path ?? "", '/', \DS), \DS);
        $content = \Cache::live($path . \json_encode($attr), function() use($time, $deep, $path) {
            return \x\block__archive($path, $time && !\is_string($time) ? '%Y.%m.%d' : $time, 0, $deep);
        }, $expire) ?? "";
        return \str_replace(' href="', ' href="' . $GLOBALS['url'] . '/', $content);
    }
    \Block::set('archive', __NAMESPACE__ . "\\block", 10);
}