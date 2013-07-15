<?php

elgg_make_sticky_form('showcase');

$guid = get_input('guid');

$showcase = new ElggShowcase($guid);

$adding = !$showcase->guid;

$editing = !$adding;

if ($editing && !$showcase->canEdit()) {
	register_error(elgg_echo('showcase:error:permissions:edit'));
	forward(REFERER);
}

if ($adding && !can_write_to_container(0, elgg_get_site_entity()->guid, 'object', 'showcase')) {
	register_error(elgg_echo('showcase:error:permissions:container'));
	forward(REFERER);
}

$address = get_input('address', '', false);
$title = get_input('title', '', false);
$description = get_input('description');
$tags = string_to_tag_array(get_input('tags', ''));

if (empty($title) || empty($address) || empty($description)) {
	register_error(elgg_echo('showcase:error:empty:fields'));
	forward(REFERER);
}

// also make screenshot mandatory if we're adding
if ($adding) {
    if ((!isset($_FILES['screenshot'])) || (!substr_count($_FILES['screenshot']['type'],'image/'))) {
        register_error(elgg_echo('showcase:error:empty:screenshot'));
        forward(REFERER);
    }
}

$showcase->owner_guid = elgg_get_logged_in_user_guid();
$showcase->access_id = ACCESS_PRIVATE; // requires admin approval before we make it public
$showcase->address = $address;
$showcase->title = $title;
$showcase->description = $description;
$showcase->tags = $tags;

// need to save first so we have a guid to use for the file
try {
	$showcase->save();
} catch (Exception $e) {
	register_error(elgg_echo('showcase:error:save:generic'));
	register_error($e->getMessage());
    forward(REFERER);
}

// Now see if we have a file icon
if ((isset($_FILES['screenshot'])) && (substr_count($_FILES['screenshot']['type'],'image/'))) {    
	$prefix = "showcase/".$showcase->guid;

	$filehandler = new ElggFile();
	$filehandler->owner_guid = $showcase->guid;
	$filehandler->setFilename($prefix . ".jpg");
	$filehandler->open("write");
	$filehandler->write(get_uploaded_file('screenshot'));
	$filehandler->close();

	$thumbtiny = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),25,25, true);
	$thumbsmall = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,40, true);
	$thumbmedium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),100,100, true);
	$thumblarge = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),200,200, false);

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

		$showcase->icontime = time();
	}
}

elgg_clear_sticky_form('showcase');

if ($adding) {
    $showcase->pending = 1;
    add_to_river('river/object/showcase/create', 'create', elgg_get_logged_in_user_guid(), $showcase->guid);
}

forward(get_input('forward', $showcase->getURL()));
