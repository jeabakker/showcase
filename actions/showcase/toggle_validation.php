<?php

$guid = get_input('guid');
$showcase = get_entity($guid);
$owner = $showcase->getOwnerEntity();

if (!elgg_instanceof($showcase, 'object', 'showcase')) {
	forward(REFERER);
}

if ($showcase->access_id == ACCESS_PRIVATE) {
	$showcase->access_id = ACCESS_PUBLIC;
	
	// notify the owner that it's been approved
	$subject = elgg_echo('showcase:approval:subject');
	$message = elgg_echo('showcase:approval:message', array(
		$owner->name,
		$showcase->getURL()
	));
	notify_user($owner, elgg_get_site_entity(), $subject, $message);
}
else {
	$showcase->access_id = ACCESS_PRIVATE;
}

$showcase->save();

forward(REFERER);