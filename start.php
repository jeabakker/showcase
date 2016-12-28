<?php

elgg_register_event_handler('init', 'system', 'showcase_init');

function showcase_init() {
	
	elgg_extend_view('elgg.css', 'showcase/showcase.css');

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
	elgg_register_plugin_hook_handler('get', 'subscriptions', 'showcase_get_subscriptions');

	elgg_register_event_handler('update', 'object', 'showcase_object_update');
	elgg_register_event_handler('delete', 'object', 'showcase_object_delete');

	// notifications
	// review is needed whenever user edits the contents, so send notifications on both 'create' and 'update' event
	elgg_register_notification_event('object', 'showcase', array('create', 'update'));
	elgg_register_plugin_hook_handler('prepare', 'notification:create:object:showcase', 'showcase_prepare_notification');
	elgg_register_plugin_hook_handler('prepare', 'notification:update:object:showcase', 'showcase_prepare_notification');

	elgg_register_page_handler('showcase', 'showcase_page_handler');

	elgg_register_menu_item('site', ElggMenuItem::factory(array(
		'name' => 'showcase',
		'href' => '/showcase',
		'text' => elgg_echo('showcase'),
	)));

	elgg_register_widget_type('showcase', elgg_echo('showcase:widget:title'), elgg_echo('showcase:widget:description'), array('profile', 'dashboard'));
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

/**
 * Prepare a notification message about a showcase site that needs to be reviewed
 *
 * @param string                          $hook         Hook name
 * @param string                          $type         Hook type
 * @param Elgg_Notifications_Notification $notification The notification to prepare
 * @param array                           $params       Hook parameters
 * @return Elgg_Notifications_Notification
 */
function showcase_prepare_notification($hook, $type, $notification, $params) {
	$entity = $params['event']->getObject();
	$language = $params['language'];

	$notification->subject = elgg_echo('showcase:notify:subject', array($entity->title), $language);
	$notification->body = elgg_echo('showcase:notify:body', array(
		$entity->getURL()
	), $language);
	$notification->summary = elgg_echo('showcase:notify:summary', array($entity->title), $language);

	return $notification;
}

/**
 * Get two random admins to notify about a showcase site in need of a review
 *
 * @param string $hook          'get'
 * @param string $type          'subscriptions'
 * @param array  $subscriptions Array containing subscriptions in the form
 *                                <user guid> => array(
 *                                    'email',
 *                                    'site',
 *                                    'etc.',
 *                                )
 * @param array  $params        Hook parameters
 * @return array $subscriptions Array containing the subscriptions
 */
function showcase_get_subscriptions($hook, $type, $subscriptions, $params) {
	$event = $params['event'];

	$object = $event->getObject();
	if (!$object instanceof ElggShowcase) {
		return $subscriptions;
	}

	$actor = $event->getActor();
	if ($actor->isAdmin()) {
		// No need to notify if the editing user was an admin
		return $subscriptions;
	}

	// Get all admins
	$admins = elgg_get_admins(array('limit' => 0));

	// Randomize the admins array
	shuffle($admins);

	// Pick two of them
	$admins = array_slice($admins, 0, 2);

	// At this point $subscriptions contains all the showcase owner's friends
	// who have subscribed to receive notifications. We don't want to inform
	// them, so we need to reset the array.
	$subscriptions = array();

	// Tell subscriptions system to send an email notification to both admins
	foreach ($admins as $admin) {
		$subscriptions[$admin->guid] = array('email');
	}

	return $subscriptions;
}
