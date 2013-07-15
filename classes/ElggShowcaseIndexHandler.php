<?php
/**
 * Handles requests to /showcase
 */
class ElggShowcaseIndexHandler {
    
    // TODO(evan): Inject views system and translation dependencies
    public function get($page) {
        
        switch ($page[0]) {
            case 'add':
                gatekeeper();
                
                elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
                elgg_push_breadcrumb(elgg_echo('showcase:add'));
                
                $title = elgg_echo('showcase:add');
                $content = elgg_view_form('showcase/add', array('enctype' => 'multipart/form-data'));
                $layout = elgg_view_layout('showcase', array(
                    'title' => $title,
                    'content' => $content
                ));
                return elgg_view_page(elgg_echo('showcase'), $layout);
                break;
            
            case 'edit':
                gatekeeper();
                $showcase = get_entity($page[1]);
                
                if (!elgg_instanceof($showcase, 'object', 'showcase')) {
                    forward('','404');
                }
                
                elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
                elgg_push_breadcrumb($showcase->title, $showcase->getURL());
                elgg_push_breadcrumb(elgg_echo('edit'));
                
                $title = elgg_echo('showcase:edit');
                $content = elgg_view_form('showcase/edit', array('enctype' => 'multipart/form-data'));
                $layout = elgg_view_layout('showcase', array(
                    'title' => $title,
                    'content' => $content
                ));
                return elgg_view_page(elgg_echo('showcase'), $layout);
                break;
            case 'view':
                // we're looking at a full view, or an error
                $showcase = get_entity($page[1]);
                
                if (!elgg_instanceof($showcase, 'object', 'showcase')) {
                    forward('','404');
                }
                
                $title = $showcase->title;
                $content = elgg_view_entity($showcase, array('full_view' => true));
                $layout = elgg_view_layout('showcase', array(
                    'title' => $title,
                    'content' => $content
                ));
                return elgg_view_page(elgg_echo('showcase'), $layout);
                break;
            default:
                elgg_push_breadcrumb(elgg_echo('showcase'));
                
                if (elgg_is_logged_in()) {
                    elgg_register_title_button();
                }
                
                $title = elgg_echo('showcase');
                $content = elgg_view('showcase/index');
                $layout = elgg_view_layout('showcase', array(
                    'title' => $title,
                    'content' => $content,
                ));
                return elgg_view_page(elgg_echo('showcase'), $layout);
                break;
        }
    }

}