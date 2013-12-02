<?php

$guid = get_input('guid');
$showcase = get_entity($guid);

if (elgg_instanceof($showcase, 'object', 'showcase') && $showcase->canEdit()) {
	
	// remove images
	$filehandler = new ElggFile();
	$filehandler->owner_guid = $showcase->guid;
	$filehandler->setFilename("showcase/{$showcase->guid}master.jpg");
	$filehandler->delete();
	
	$filehandler->setFilename("showcase/{$showcase->guid}large.jpg");
	$filehandler->delete();
	
	$filehandler->setFilename("showcase/{$showcase->guid}medium.jpg");
	$filehandler->delete();
	
	$filehandler->setFilename("showcase/{$showcase->guid}small.jpg");
	$filehandler->delete();
	
	$filehandler->setFilename("showcase/{$showcase->guid}tiny.jpg");
	$filehandler->delete();
	
	
	if ($showcase->delete()) {
		system_message(elgg_echo('showcase:message:deleted'));
		forward("showcase");
	} else {
		register_error(elgg_echo('showcase:error:cannot_delete'));
	}
} else {
	register_error(elgg_echo('showcase:error:not_found'));
}

forward(REFERER);