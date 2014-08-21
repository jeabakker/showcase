<?php

elgg_register_event_handler('init', 'system', 'showcase_init');

function showcase_init() {
	elgg_extend_view('css/elgg', 'css/showcase');
	
	$js = elgg_get_simplecache_url('js', 'showcase/js');
	elgg_register_simplecache_view('js/showcase/js');
	elgg_register_js('showcase', $js);
	
	$masonry = elgg_get_simplecache_url('js', 'showcase/masonry');
	elgg_register_simplecache_view('js/showcase/masonry');
	elgg_register_js('showcase/masonry', $masonry);
	
	//general
	elgg_register_entity_type("object", 'showcase');

	//actions
	$actions_base = dirname(__FILE__) . "/actions/showcase";
	elgg_register_action("showcase/add", "$actions_base/save.php");
	elgg_register_action("showcase/edit", "$actions_base/save.php");
	elgg_register_action("showcase/delete", "$actions_base/delete.php");
	elgg_register_action("showcase/screenshot/delete", "$actions_base/screenshot_delete.php");
	elgg_register_action("showcase/toggle_validation", "$actions_base/toggle_validation.php", 'admin');
	elgg_register_action("showcase/toggle_featured", "$actions_base/toggle_featured.php", 'admin');

	//handlers
	elgg_register_plugin_hook_handler('entity:icon:url', 'object', 'showcase_icon_url_handler');
	elgg_register_plugin_hook_handler('entity:url', 'object', 'showcase_url_handler');
	elgg_register_plugin_hook_handler('register', 'menu:entity', 'showcase_entity_menu');
	elgg_register_plugin_hook_handler('container_permissions_check', 'object', 'showcase_container_permissions');
	
	elgg_register_event_handler('update', 'object', 'showcase_object_update');
	elgg_register_event_handler('delete', 'object', 'showcase_object_delete');

	elgg_register_page_handler('showcase', 'showcase_page_handler');
    
	elgg_register_menu_item('site', ElggMenuItem::factory(array(
		'name' => 'showcase',
		'href' => '/showcase',
		'text' => elgg_echo('showcase'),
	)));
	
	elgg_register_widget_type('showcase', elgg_echo('showcase:widget:title'), elgg_echo('showcase:widget:description'), 'profile,dashboard');
}

function showcase_page_handler($page) {
	switch ($page[0]) {
		case 'add':
			gatekeeper();
            
			elgg_set_page_owner_guid($page[1]);
            elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb(elgg_echo('showcase:add'));
                
            $title = elgg_echo('showcase:add');
            $content = elgg_view_form('showcase/add', array('enctype' => 'multipart/form-data'));
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false,
				'sidebar' => elgg_view('showcase/sidebar')
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
                
			elgg_set_page_owner_guid($showcase->owner_guid);
            elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb($showcase->title, $showcase->getURL());
            elgg_push_breadcrumb(elgg_echo('edit'));
                
            $title = elgg_echo('showcase:edit');
            $content = elgg_view_form('showcase/edit', array('enctype' => 'multipart/form-data'), array('entity' => $showcase));
            $layout = elgg_view_layout('content', array(
                'title' => $title,
                'content' => $content,
				'filter' => false,
				'sidebar' => elgg_view('showcase/sidebar')
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
			
			elgg_set_page_owner_guid($showcase->owner_guid);
			elgg_push_breadcrumb(elgg_echo('showcase'), elgg_get_site_url() . 'showcase');
            elgg_push_breadcrumb($showcase->title, $showcase->getURL());
                
            $title = $showcase->title;
			$title_link = elgg_view('output/url', array(
				'text' => $title,
				'href' => $showcase->address,
				'target' => '_blank'
			));
            $content = elgg_view_entity($showcase, array('full_view' => true));
			
			if ($showcase->allow_comments) {
				$content .= elgg_view_comments($showcase);
			}
			
            $layout = elgg_view_layout('content', array(
                'title' => $title_link,
                'content' => $content,
				'filter' => false,
				'sidebar' => elgg_view('showcase/sidebar')
            ));
            echo elgg_view_page($title, $layout);
			return true;
            break;
		case 'icon':
			$img = get_entity($page[1]);
			$size = $page[2];
			if (!elgg_instanceof($img, 'object', 'showcaseimg')) {
                forward('','404');
            }
			
			if ($size == 'original') {
				$size = '';
			}
			
			$img->setFilename($img->file_prefix . $size . '.jpg');
			$filename = $img->getFilenameOnFilestore();
			
			$filesize = @filesize($filename);
			if ($filesize) {
				header("Content-type: image/jpeg");
				header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', strtotime("+6 months")), true);
				header("Pragma: public");
				header("Cache-Control: public");
				header("Content-Length: $filesize");
				readfile($filename);
				exit;
			}
			break;
		case 'owner':
			elgg_push_breadcrumb(elgg_echo('showcase'));
			
			$username = urldecode($page[1]);
			$user = get_user_by_username($username);
			if (!$user) {
				return false;
			}
			elgg_set_page_owner_guid($user->guid);
			
			if (elgg_is_logged_in() && elgg_get_logged_in_user_guid() == $user->guid) {
                elgg_register_title_button();
            }
			
			set_input('filter', 'owner');
			set_input('owner_guid', $user->guid);
            
			if (include(dirname(__FILE__) . '/pages/showcase/list.php')) {
				return true;
			}
            	
			break;
			
		case 'friends':
			elgg_push_breadcrumb(elgg_echo('showcase'));
			
			$username = urldecode($page[1]);
			$user = get_user_by_username($username);
			if (!$user) {
				return false;
			}
			elgg_set_page_owner_guid($user->guid);
			
			set_input('filter', 'friends');
			set_input('owner_guid', $user->guid);
            
			if (include(dirname(__FILE__) . '/pages/showcase/list.php')) {
				return true;
			}
			break;
        default:
            elgg_push_breadcrumb(elgg_echo('showcase'));
              
            if (elgg_is_logged_in()) {
                elgg_register_title_button();
            }
            
			if (include(dirname(__FILE__) . '/pages/showcase/list.php')) {
				return true;
			}
            
            break;
    }
	
	return false;
}

/**
 * Handles showcase URLs
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string
 */
function showcase_url_handler($hook, $type, $url, $params) {
	$entity = $params['entity'];

	if ($entity instanceof ElggShowcase) {
		$url = "/showcase/view/$entity->guid/" . elgg_get_friendly_title($entity->title);
	}

	return $url;
}


function showcase_icon_url_handler($hook, $type, $return, $params) {
	if (!elgg_instanceof($params['entity'], 'object', 'showcase')) {
		return $return;
	}
	
	// get first (oldest) image
	$img = showcase_get_default_image($params['entity']);
	
	if ($img) {
		$filename = md5($img->time_created);
		return "showcase/icon/{$img->guid}/{$params['size']}/{$filename}.jpg";
	}
	
	return $return;
}


function showcase_entity_menu($hook, $type, $return, $params) {
	if (!elgg_is_admin_logged_in()) {
		return $return;
	}
	
	if (!elgg_instanceof($params['entity'], 'object', 'showcase')) {
		return $return;
	}
	
	$text = elgg_echo('showcase:unvalidate');
	if ($params['entity']->access_id == ACCESS_PRIVATE) {
		$text = elgg_echo('showcase:validate');
	}
	$href = elgg_add_action_tokens_to_url('action/showcase/toggle_validation?guid='.$params['entity']->guid);
	
	$validate = new ElggMenuItem('validate', $text, $href);
	$return[] = $validate;
	
	
	$text = elgg_echo('showcase:feature');
	if ($params['entity']->showcase_featured) {
		$text = elgg_echo('showcase:unfeature');
	}
	$href = elgg_add_action_tokens_to_url('action/showcase/toggle_featured?guid='.$params['entity']->guid);
	
	$feature = new ElggMenuItem('feature', $text, $href);
	$return[] = $feature;
	
	return $return;
}



function showcase_object_update($event, $type, $object) {
	if (!elgg_instanceof($object, 'object', 'showcase')) {
		return;
	}
	
	// update the access ID of attached screenshots
	$images = elgg_get_entities_from_relationship(array(
		'type' => 'object',
		'subtype' => 'showcaseimg',
		'relationship' => 'screenshot',
		'relationship_guid' => $object->guid,
		'inverse_relationship' => true,
		'limit' => false
	));
	
	foreach ($images as $img) {
		if ($img->access_id != $object->access_id) {
			$img->access_id = $object->access_id;
			$img->save();
		}
	}
}


function showcase_object_delete($event, $type, $object) {
	// if deleting showcase delete associated images
	if (elgg_instanceof($object, 'object', 'showcase')) {
		$images = elgg_get_entities_from_relationship(array(
			'type' => 'object',
			'subtype' => 'showcaseimg',
			'relationship' => 'screenshot',
			'relationship_guid' => $object->guid,
			'inverse_relationship' => true,
			'limit' => false
		));
		
		foreach ($images as $img) {
			$img->delete();
		}
	}
	
	// if deleting an image, clean up the files
	if (elgg_instanceof($object, 'object', 'showcaseimg')) {
		// remove images
		$filehandler = new ElggFile();
		$filehandler->owner_guid = $object->owner_guid;
		$filehandler->setFilename("{$object->file_prefix}.jpg");
		$filehandler->delete();
		
		$filehandler->setFilename("{$object->file_prefix}master.jpg");
		$filehandler->delete();
		
		$filehandler->setFilename("{$object->file_prefix}large.jpg");
		$filehandler->delete();
	
		$filehandler->setFilename("{$object->file_prefix}medium.jpg");
		$filehandler->delete();
	
		$filehandler->setFilename("{$object->file_prefix}small.jpg");
		$filehandler->delete();
	
		$filehandler->setFilename("{$object->file_prefix}tiny.jpg");
		$filehandler->delete();
		
		// now regenerate the default image size cache
		$showcase = showcase_get_showcase_from_image($object);
		$showcase->featured_image_size_cache = 0;
	}
}


function showcase_container_permissions($hook, $type, $return, $params) {
	if ($params['subtype'] != 'showcase') {
		return $return;
	}
	
	return true;
}


function showcase_set_featured_dimensions($showcase) {
	if (!elgg_instanceof($showcase, 'object', 'showcase')) {
		return true;
	}
	
	// we're caching the dimensions of the default image
	// as they may take different sizes (not squared) and we want to know
	// to set dimensions for masonry
	$image = showcase_get_default_image($showcase);
	
	$sizes = array('tiny', 'small', 'medium', 'large', 'master');
	
	foreach ($sizes as $size) {
		$image->setFilename($image->file_prefix . $size . '.jpg');
		$imageinfo = getimagesize($image->getFilenameOnFilestore());
		
		$width = 'default_size_cache_' . $size . '_w';
		$height = 'default_size_cache_' . $size . '_h';
		
		if ($imageinfo[0] && $imageinfo[1]) {			
			$showcase->$width = $imageinfo[0];
			$showcase->$height = $imageinfo[1];
		}
		else {
			$showcase->$width = '';
			$showcase->$height = '';
		}
	}
	
	$showcase->featured_image_size_cache = 1;
}

function showcase_get_default_image($showcase) {
	// get first (oldest) image
	$img = elgg_get_entities_from_relationship(array(
		'type' => 'object',
		'subtype' => 'showcaseimg',
		'relationship' => 'screenshot',
		'relationship_guid' => $showcase->guid,
		'inverse_relationship' => true,
		'limit' => 1,
		'order_by' => 'e.time_created ASC'
	));
	
	if ($img[0]) {
		return $img[0];
	}
	
	return false;
}


function showcase_get_showcase_from_image($image) {
	$showcase = elgg_get_entities_from_relationship(array(
		'type' => 'object',
		'subtype' => 'showcase',
		'relationship' => 'screenshot',
		'relationship_guid' => $image->guid,
		'limit' => 1
	));
	
	if ($showcase[0]) {
		return $showcase[0];
	}
	
	return false;
}


