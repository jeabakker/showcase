<?php

elgg_make_sticky_form('showcase');

$guid = get_input('guid');
$container_guid = get_input('container_guid');
$owner = get_user($container_guid);

if (!$owner || !can_write_to_container(0, $container_guid, 'object', 'showcase')) {
	register_error(elgg_echo('showcase:error:permissions:container'));
	forward(REFERER);
}

$showcase = new ElggShowcase($guid);

$adding = !$showcase->guid;

$editing = !$adding;

$file_keys = array();
if ($_FILES['screenshot']['tmp_name']) {
	$file_keys = array_keys($_FILES['screenshot']['tmp_name']);
	foreach ($_FILES['screenshot']['tmp_name'] as $key => $tmp_name) {
		$size = getimagesize($_FILES['screenshot']['tmp_name'][$key]);
		
		// check for image errors
		if(!substr_count($_FILES['screenshot']['type'][$key],'image/') || $_FILES['screenshot']['error'][$key]) {
			if(($k = array_search($key, $file_keys)) !== false) {
				unset($file_keys[$key]);
			}
		}
		elseif (!$size || $size[0] > 2048 || $size[1] > 1536) {
			if(($k = array_search($key, $file_keys)) !== false) {
				unset($file_keys[$key]);
				system_message(elgg_echo('showcase:error:image:size'));
			}
		}
	}
}

if ($editing && !$showcase->canEdit()) {
	register_error(elgg_echo('showcase:error:permissions:edit'));
	forward(REFERER);
}

if ($adding && !can_write_to_container(0, elgg_get_site_entity()->guid, 'object', 'showcase')) {
	register_error(elgg_echo('showcase:error:permissions:container'));
	forward(REFERER);
}

$address = get_input('address', '', false);
$title = htmlspecialchars(get_input('title', '', false), ENT_QUOTES, 'UTF-8');
$description = get_input('description');
$tags = string_to_tag_array(get_input('tags', ''));
$allow_comments = get_input('allow_comments', 1);

if (empty($title) || empty($address) || empty($description)) {
	register_error(elgg_echo('showcase:error:empty:fields'));
	forward(REFERER);
}

// don't use elgg_normalize_url() because we don't want
// relative links resolved to this site.
if ($address && !preg_match("#^((ht|f)tps?:)?//#i", $address)) {
	$address = "http://$address";
}

// see https://bugs.php.net/bug.php?id=51192
$php_5_2_13_and_below = version_compare(PHP_VERSION, '5.2.14', '<');
$php_5_3_0_to_5_3_2 = version_compare(PHP_VERSION, '5.3.0', '>=') &&
		version_compare(PHP_VERSION, '5.3.3', '<');

$validated = false;
if ($php_5_2_13_and_below || $php_5_3_0_to_5_3_2) {
	$tmp_address = str_replace("-", "", $address);
	$validated = filter_var($tmp_address, FILTER_VALIDATE_URL);
} else {
	$validated = filter_var($address, FILTER_VALIDATE_URL);
}
// above doesn't check for tld, which we will require
if ($validated) {
	$parts = parse_url($address);
	if (count(explode('.', $parts['host'])) < 2) {
		$validated = false;
	}
}

if (!$validated) {
	register_error(elgg_echo('showcase:error:invalid:url'));
	forward(REFERER);
}

// also make screenshot mandatory if we're adding
if ($adding) {
	if (!$file_keys) {
		register_error(elgg_echo('showcase:error:empty:screenshot'));
        forward(REFERER);
	}
}

$access_id = ACCESS_PRIVATE;
if (elgg_is_admin_logged_in()) {
	$access_id = $adding ? ACCESS_PUBLIC : $showcase->access_id;
}

$showcase->owner_guid = $container_guid;
$showcase->container_guid = $container_guid;
$showcase->access_id = $access_id;
$showcase->address = $address;
$showcase->title = $title;
$showcase->description = $description;
$showcase->tags = $tags;
$showcase->allow_comments = $allow_comments;

// need to save first so we have a guid to use for the file
try {
	$showcase->save();
} catch (Exception $e) {
	register_error(elgg_echo('showcase:error:save:generic'));
	register_error($e->getMessage());
    forward(REFERER);
}

// Now see if we have a file icon
if ($file_keys) {
	$time = time();
	$invalid = 0;
	foreach ($file_keys as $key) {
		
		$prefix = "showcase/".$time.$key;
		$img_orig = get_resized_image_from_existing_file($_FILES['screenshot']['tmp_name'][$key],2048,1536, false);
		$filehandler = new ElggShowcaseImg();
		$filehandler->access_id = elgg_is_admin_logged_in() ? ACCESS_PUBLIC : ACCESS_PRIVATE;
		$filehandler->owner_guid = $container_guid;
		$filehandler->setFilename($prefix . ".jpg");
		$filehandler->open("write");
		$filehandler->write($img_orig);
		$filehandler->close();
		$filehandler->save();
		
		add_entity_relationship($filehandler->guid, 'screenshot', $showcase->guid);

		$thumbtiny = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),25,25, true);
		$thumbsmall = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,40, true);
		$thumbmedium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),100,100, true);
		$thumblarge = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),200,200, false);
		$thumbmaster = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),700,400, false);

		if ($thumbtiny) {

			$thumb = new ElggFile();
			$thumb->owner_guid = $container_guid;
			$thumb->setMimeType('image/jpeg');
			
			$thumb->setFilename($prefix."tiny.jpg");
			$thumb->open("write");
			$thumb->write($thumbtiny);
			$thumb->close();

			$thumb->setFilename($prefix."small.jpg");
			$thumb->open("write");
			$thumb->write($thumbsmall);
			$thumb->close();

			$thumb->setFilename($prefix."medium.jpg");
			$thumb->open("write");
			$thumb->write($thumbmedium);
			$thumb->close();

			$thumb->setFilename($prefix."large.jpg");
			$thumb->open("write");
			$thumb->write($thumblarge);
			$thumb->close();
		
			$thumb->setFilename($prefix."master.jpg");
			$thumb->open("write");
			$thumb->write($thumbmaster);
			$thumb->close();
		}
		
		$filehandler->file_prefix = $prefix;
		
		if ($invalid) {
			system_message(elgg_echo('showcase:invalid:screenshot:size', array($invalid)));
		}
	}
}

showcase_set_featured_dimensions($showcase);

elgg_clear_sticky_form('showcase');

forward(get_input('forward', $showcase->getURL()));
