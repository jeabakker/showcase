<?php

$guid = get_input('guid');
$showcase = get_entity($guid);

if (!elgg_instanceof($showcase, 'object', 'showcase')) {
	forward(REFERER);
}

if ($showcase->showcase_featured) {
	$showcase->showcase_featured = 0;
	system_message(elgg_echo('showcase:action:unfeatured'));
}
else {
	$showcase->showcase_featured = 1;
	system_message(elgg_echo('showcase:action:featured'));
}

forward(REFERER);