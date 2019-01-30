<?php

namespace fn {
    function archive(string $path) {
        $out = "";
        if (!\is_dir($path)) {
            return $out;
        }
        $files = \Get::pages($path, 'page', [1, 'title'], 'path');
        if ($files->count()) {
            $out .= '<ul>';
            foreach ($files as $file) {
                if (\Path::N($file) === '$') {
                    continue; // Ignore placeholder page…
                }
                $page = new \Page($file);
                $url = $page->url;
                $title = $page->title;
                $current = $GLOBALS['URL']['clean'] === $url;
                $out .= '<li' . ($current ? ' class="current"' : "") . '>';
                if ($current) {
                    $out .= '<span>' . $title . '</span>';
                } else {
                    $out .= '<a href="' . \URL::short($url, false) . '">' . $title . '</a>';
                }
                $out .= archive(\Path::F($file));
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }
}

namespace fn\block {
    function archive($a, $b) {
        $path = \rtrim(PAGE . DS . \strtr($b['path'] ?? "", '/', DS), DS);
        $cache = \Cache::expire($path) ? \Cache::set($path, function() use($path) {
            return \fn\archive($path);
        }) : \Cache::get($path, "");
        return \str_replace(' href="', ' href="' . $GLOBALS['URL']['$'] . '/', $cache);
    }
    \Block::set('archive', __NAMESPACE__ . "\\archive", 10);
}