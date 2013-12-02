<?php

elgg_load_js('lightbox');
elgg_load_css('lightbox');

$showcase = $vars['entity'];
$owner = $showcase->getOwnerEntity();

$images = elgg_get_entities_from_relationship(array(
	'type' => 'object',
	'subtype' => 'showcaseimg',
	'relationship' => 'screenshot',
	'relationship_guid' => $vars['entity']->guid,
	'inverse_relationship' => true,
	'limit' => 9,
	'order_by' => 'e.time_created ASC'
));


$url = '<div><strong>' . elgg_echo('showcase:website:url') . '</strong>';
$url .= elgg_view('output/url', array(
	'text' => $showcase->address,
	'href' => $showcase->address
));
$url .= '</div>';

$gallery = '';
if ($images) {
	$gallery .= elgg_view('output/longtext', array(
		'value' => elgg_echo('showcase:gallery:help'),
		'class' => 'elgg-subtext'
	));
	$gallery .= '<ul class="elgg-gallery elgg-showcase-screenshots">';
	foreach ($images as $img) {
		$thumb_url = elgg_get_site_url() . "showcase/icon/{$img->guid}/large/" . md5($img->time_created) . '.jpg';
		$full_url = elgg_get_site_url() . "showcase/icon/{$img->guid}/original/" . md5($img->time_created) . '.jpg';
		$gallery .= '<li>';
		$gallery .= "<a class=\"elgg-showcase-screenshot elgg-lightbox\" href=\"$full_url\" rel=\"showcase-gallery\"><img src=\"$thumb_url\" alt=\"$img->title\" title=\"$img->title\"/></a>";
		$gallery .= '</li>';
	}
	$gallery .= '</ul>';
}

$owner_icon = elgg_view_entity_icon($owner, 'tiny');
$owner_link = elgg_view('output/url', array(
	'href' => $owner->getURL(),
	'text' => $owner->name,
	'is_trusted' => true,
));
$author_text = elgg_echo('byline', array($owner_link));
$date = elgg_view_friendly_time($showcase->time_created);

$categories = elgg_view('output/categories', $vars);

$body = $url . $gallery . elgg_view('output/longtext', array('value' => $showcase->description));

$metadata = elgg_view_menu('entity', array(
	'entity' => $showcase,
	'handler' => 'showcase',
	'sort_by' => 'priority',
	'class' => 'elgg-menu-hz',
));

// do not show the metadata and controls in widget view
if (elgg_in_context('widgets')) {
	$metadata = '';
}

$subtitle = "$author_text $date $categories";


$params = array(
	'entity' => $showcase,
	'title' => false,
	'metadata' => $metadata,
	'subtitle' => $subtitle,
);
$params = $params + $vars;
$summary = elgg_view('object/elements/summary', $params);

echo elgg_view('object/elements/full', array(
	'summary' => $summary,
	'icon' => $owner_icon,
	'body' => $body,
));