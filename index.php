<?php namespace x\block__archive;

function block__archive(string $content, array $data = []) {
    \extract(\array_replace([
        'deep' => 4,
        'route' => "",
        'time' => false
    ], \e($data[2] ?? [])), \EXTR_SKIP);
    $create = static function (string $folder, int $i = 0) use ($deep, $route, $time, &$create) {
        $out = "";
        if (!\is_dir($folder)) {
            return $out;
        }
        $pages = \Pages::from($folder)->sort($time ? [-1, 'time'] : [1, 'title']);
        if ($pages->count()) {
            $out .= '<ul>';
            foreach ($pages as $page) {
                $url = $page->url;
                $title = $page->title;
                $out .= '<li>';
                if ($time) {
                    $out .= '<time datetime="' . $page->time->ISO8601 . '">' . $page->time(true === $time ? '%Y/%m/%d' : $time) . '</time>&#x2003;';
                }
                $out .= '<a href="' . \short($page->url) . '">' . $page->title . '</a>';
                if ($i < $deep) {
                    $out .= $create(\dirname($page->path) . \D . \pathinfo($page->path, \PATHINFO_FILENAME), $i + 1);
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    };
    return $create(\rtrim(\LOT . \D . 'page' . \strtr($route ?? "", '/', \D), \D));
}

\Hook::set('block.archive', __NAMESPACE__ . "\\block__archive", 0);