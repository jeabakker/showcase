<?php

elgg_make_sticky_form('showcase');

$guid = get_input('guid');

$showcase = new ElggShowcase($guid);

$adding = !$showcase->guid;

$editing = !$adding;

$file_keys = array();
if ($_FILES['screenshot']['tmp_name']) {
	$file_keys = array_keys($_FILES['screenshot']['tmp_name']);
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
if (!$validated) {
	register_error(elgg_echo('showcase:error:invalid:url'));
	forward(REFERER);
}

// also make screenshot mandatory if we're adding
if ($adding) {
	$screenshot = false;
	if ($file_keys) {
		foreach ($file_keys as $key) {
			if (substr_count($_FILES['screenshot']['type'][$key],'image/') && !$_FILES['screenshot']['error'][$key]) {
				$screenshot = true;
				break;
			}
		}
	}
	
	if (!$screenshot) {
		register_error(elgg_echo('showcase:error:empty:screenshot'));
        forward(REFERER);
	}
}

$showcase->owner_guid = elgg_get_logged_in_user_guid();
$showcase->access_id = ACCESS_PRIVATE;
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
	foreach ($file_keys as $key) {
		$prefix = "showcase/".$time.$key;

		$filehandler = new ElggShowcaseImg();
		$filehandler->owner_guid = $showcase->guid;
		$filehandler->setFilename($prefix . ".jpg");
		$filehandler->open("write");
		$filehandler->write(file_get_contents($_FILES['screenshot']['tmp_name'][$key]));
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
			$thumb->owner_guid = $showcase->guid;
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
	}
}

elgg_clear_sticky_form('showcase');

forward(get_input('forward', $showcase->getURL()));
