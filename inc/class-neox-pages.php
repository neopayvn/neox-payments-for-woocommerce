<?php
if (!defined('ABSPATH')) {
    exit;
}

class NeoX_Pages
{
    static function get_pages()
    {
        $pagesList = array();
        $pages = get_pages();
        if (!empty($pages)) {
            foreach ($pages as $page) {
                $pagesList[$page->ID] = $page->post_title;
            }
        }
        return $pagesList;
    }
}
