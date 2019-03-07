<?php

namespace fn {
    function archive(string $path, $date, $i, $deep) {
        $out = "";
        if (!\is_dir($path)) {
            return $out;
        }
        $files = \Get::pages($path, 'page', $date ? [-1, 'time'] : [1, 'title'], 'path');
        if ($files->count()) {
            $out .= '<ul>';
            foreach ($files as $file) {
                if (\Path::N($file) === '$') {
                    continue; // Ignore placeholder page…
                }
                $page = new \Page($file);
                $url = $page->url;
                $title = $page->title;
                $out .= '<li>';
                if ($date) {
                    $out .= '<time datetime="' . $page->time->ISO8601 . '">' . $page->time($date) . '</time>&#x2003;';
                }
                $out .= '<a href="' . \URL::short($url, false) . '">' . $title . '</a>';
                if ($i < $deep) {
                    $out .= archive(\Path::F($file), $date, $i + 1, $deep);
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }
}

namespace fn\block {
    function archive($content, $attr) {
        extract(\extend([
            'date' => false,
            'deep' => 2,
            'path' => ""
        ], $attr), \EXTR_SKIP);
        // Refresh cache by adding `?cache=0` or `?cache=false` to the current URL
        $expire = \HTTP::is('get', 'cache') && !\HTTP::get('cache') ? 0 : '1 year';
        $path = \rtrim(PAGE . DS . \strtr($path ?? "", '/', DS), DS);
        $content = \Cache::alt($path . \json_encode($attr), function() use($date, $deep, $path) {
            return \fn\archive($path, $date && !\is_string($date) ? '%Y%.%M%.%D%' : $date, 0, $deep);
        }, $expire) ?? "";
        return \str_replace(' href="', ' href="' . $GLOBALS['URL']['$'] . '/', $content);
    }
    \Block::set('archive', __NAMESPACE__ . "\\archive", 10);
}