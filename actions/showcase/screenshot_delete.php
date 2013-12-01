<?php

$guid = get_input('guid');
$img = get_entity($guid);

if (!elgg_instanceof($img, 'object', 'showcaseimg') || !$img->canEdit()) {
	register_error(elgg_echo('showcase:error:invalid:guid'));
	forward(REFERER);
}

$img->delete();

forward(REFERER);