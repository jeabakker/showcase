<?php

$showcase = $vars['entity'];

if (!$showcase->featured_image_size_cache) {
	showcase_set_featured_dimensions($showcase);
}

$style = '';
if ($showcase->default_size_cache_large_w && $showcase->default_size_cache_large_h) {
	$style = "width:{$showcase->default_size_cache_large_w}px; height:{$showcase->default_size_cache_large_h}px;";
}

// note, not using elgg_view_entity_icon to avoid forced image size
$icon = elgg_view('output/url', array(
	'text' => elgg_view('output/img', array(
		'src' => $showcase->getIconURL('large'),
		'alt' => $showcase->title,
		'title' => $showcase->title,
		'style' => $style
	)),
	'href' => $showcase->getURL()
));

elgg_view_entity_icon($showcase, 'large');

echo '<h3>' . elgg_view('output/url', array(
	'text' => $showcase->title,
	'href' => $showcase->getURL()
)). '</h3>';
echo $icon;
