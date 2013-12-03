<?php

// short circuit for a different view for featured
if (elgg_in_context('showcase_featured')) {
	echo elgg_view('object/showcase/featured', $vars);
	return;
}

$showcase = $vars['entity'];

$excerpt = elgg_get_excerpt($showcase->description);

$metadata = elgg_view_menu('entity', array(
	'entity' => $showcase,
	'handler' => 'showcase',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

// do not show the metadata and controls in widget view
$iconsize = 'medium';
if (elgg_in_context('widgets')) {
	$metadata = '';
	$iconsize = 'small';
}

$icon = elgg_view_entity_icon($showcase, $iconsize);

$params = array(
	'entity' => $showcase,
	'metadata' => $metadata,
//		'subtitle' => $subtitle,
	'content' => $excerpt,
);
$params = $params + $vars;
$list_body = elgg_view('object/elements/summary', $params);

echo elgg_view_image_block($icon, $list_body);