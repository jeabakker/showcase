<?php

function showcase_init() {
	//general
	elgg_register_entity_type("object", 'showcase');

	elgg_register_admin_menu_item('administer', 'pending', 'showcase');
	elgg_register_admin_menu_item('administer', 'featured', 'showcase');

	//actions
	$actions_base = dirname(__FILE__) . "/actions/showcase";
	elgg_register_action("showcase/add", "$actions_base/save.php");
	elgg_register_action("showcase/delete", "$actions_base/delete.php");

	//handlers
	elgg_register_entity_url_handler('object', 'showcase', 'showcase_url_handler');
	
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', 'showcase_icon_url_handler');

	elgg_register_page_handler('showcase', 'showcase_page_handler');
    
	elgg_register_menu_item('site', ElggMenuItem::factory(array(
		'name' => 'showcase',
		'href' => '/showcase',
		'text' => elgg_echo('showcase'),
	)));
}

function showcase_page_handler($page) {
	switch ($page[0]) {
		case 'add':
			gatekeeper();
               
            elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb(elgg_echo('showcase:add'));
                
            $title = elgg_echo('showcase:add');
            $content = elgg_view_form('showcase/add', array('enctype' => 'multipart/form-data'));
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false
            ));
            echo elgg_view_page(elgg_echo('showcase'), $layout);
			return true;
            break;
            
        case 'edit':
            gatekeeper();
            $showcase = get_entity($page[1]);
               
            if (!elgg_instanceof($showcase, 'object', 'showcase') || !$showcase->canEdit()) {
               forward('','404');
            }
                
            elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb($showcase->title, $showcase->getURL());
            elgg_push_breadcrumb(elgg_echo('edit'));
                
            $title = elgg_echo('showcase:edit');
            $content = elgg_view_form('showcase/edit', array('enctype' => 'multipart/form-data'), array('entity' => $showcase));
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false
            ));
            echo elgg_view_page(elgg_echo('showcase'), $layout);
			return true;
            break;
        case 'view':
            // we're looking at a full view, or an error
            $showcase = get_entity($page[1]);
             
            if (!elgg_instanceof($showcase, 'object', 'showcase')) {
                forward('','404');
            }
			
			elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb($showcase->title, $showcase->getURL());
                
            $title = $showcase->title;
            $content = elgg_view_entity($showcase, array('full_view' => true));
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false
            ));
            echo elgg_view_page(elgg_echo('showcase'), $layout);
			return true;
            break;
		case 'icon':
			$showcase = get_entity($page[1]);
			if (!elgg_instanceof($showcase, 'object', 'showcase')) {
                forward('','404');
            }
			
			$filehandler = new ElggFile();
			$filehandler->owner_guid = $showcase->guid;
			$filehandler->setFilename("showcase/{$showcase->guid}{$page[2]}.jpg");
			$filename = $filehandler->getFilenameOnFilestore();
			
			$size = @filesize($filename);
			if ($size) {
				header("Content-type: image/jpeg");
				header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
				header("Pragma: public");
				header("Cache-Control: public");
				header("Content-Length: $size");
				readfile($filename);
				exit;
			}
			break;
        default:
            elgg_push_breadcrumb(elgg_echo('showcase'));
              
            if (elgg_is_logged_in()) {
                elgg_register_title_button();
            }
                
            $title = elgg_echo('showcase');
            $content = elgg_view('showcase/index');
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false
            ));
            echo elgg_view_page(elgg_echo('showcase'), $layout);
			return true;
            break;
    }
	
	return false;
}

function showcase_url_handler($object) {
	return "/showcase/view/$object->guid/" . elgg_get_friendly_title($object->title);
}


function showcase_icon_url_handler($hook, $type, $return, $params) {
	if (!elgg_instanceof($params['entity'], 'object', 'showcase')) {
		return $return;
	}
	
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $params['entity']->guid;
	$filehandler->setFilename("showcase/{$params['entity']->guid}{$params['size']}.jpg");
	
	if ($filehandler->exists()) {
		return "showcase/icon/{$params['entity']->guid}/{$params['size']}/{$params['entity']->icontime}.jpg";
	}
	
	return $return;
}

elgg_register_event_handler('init', 'system', 'showcase_init');
