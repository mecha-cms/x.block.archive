<?php

namespace _\lot\x\block\archive {
    function content(string $path, $date, $i, $deep) {
        $out = "";
        if (!\is_dir($path)) {
            return $out;
        }
        $pages = \Pages::from($path)->sort($date ? [-1, 'time'] : [1, 'title']);
        if ($pages->count()) {
            $out .= '<ul>';
            foreach ($pages as $page) {
                $url = $page->url;
                $title = $page->title;
                $out .= '<li>';
                if ($date) {
                    $out .= '<time datetime="' . $page->time->ISO8601 . '">' . $page->time($date) . '</time>&#x2003;';
                }
                $out .= '<a href="' . \URL::short($url, false) . '">' . $title . '</a>';
                if ($i < $deep) {
                    $out .= content(\Path::F($page->path), $date, $i + 1, $deep);
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }
}

namespace _\lot\x\block {
    function archive($content, $attr) {
        extract(\array_replace([
            'date' => false,
            'deep' => 4,
            'path' => ""
        ], $attr), \EXTR_SKIP);
        // Refresh cache by adding `?cache=0` or `?cache=false` to the current URL
        $expire = \Request::is('get', 'cache') && !\Get::get('cache') ? 0 : '1 year';
        $path = \rtrim(PAGE . DS . \strtr($path ?? "", '/', DS), DS);
        $content = \Cache::live($path . \json_encode($attr), function() use($date, $deep, $path) {
            return \_\lot\x\block\archive\content($path, $date && !\is_string($date) ? '%Y.%m.%d' : $date, 0, $deep);
        }, $expire) ?? "";
        return \str_replace(' href="', ' href="' . $GLOBALS['url'] . '/', $content);
    }
    \Block::set('archive', __NAMESPACE__ . "\\archive", 10);
}