<?php

elgg_load_js('showcase/masonry');

$showcase = $vars['entity'];
$owner = $showcase->getOwnerEntity();
$owner_link = elgg_view('output/url', array(
	'text' => $owner->name,
	'href' => $owner->getURL()
));

// note, not using elgg_view_entity_icon to avoid forced image size
$icon = elgg_view('output/url', array(
	'text' => elgg_view('output/img', array(
		'src' => $showcase->getIconURL('large'),
		'alt' => $showcase->title,
		'title' => $showcase->title
	)),
	'href' => $showcase->getURL()
));

elgg_view_entity_icon($showcase, 'large');

echo elgg_view('output/url', array(
	'text' => $showcase->title,
	'href' => $showcase->getURL()
));
echo '<br>';
echo $icon;