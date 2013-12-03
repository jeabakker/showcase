<?php

elgg_load_js('showcase/masonry');

$showcase = $vars['entity'];
$owner = $showcase->getOwnerEntity();
$owner_link = elgg_view('output/url', array(
	'text' => $owner->name,
	'href' => $owner->getURL()
));

$icon = elgg_view_entity_icon($showcase, 'large');

echo elgg_view('output/url', array(
	'text' => $showcase->title,
	'href' => $showcase->getURL()
));
echo '<br>';
echo $icon;