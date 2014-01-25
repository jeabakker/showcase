<?php
//elgg_load_js('showcase/imagesloaded');
elgg_load_js('showcase/masonry');

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

echo elgg_view('output/url', array(
	'text' => $showcase->title,
	'href' => $showcase->getURL()
));
echo '<br>';
echo $icon;

// only want this added the first time
if (elgg_get_config('featured-masonry')) {
	return;
}

elgg_set_config('featured-masonry', 1);
?>
<script>
	$(document).ready(function() {
		
		// call initially for cached images
		$('.showcase-featured-list').masonry();
		
		$('.showcase-featured-list img').each(function() {
			$(this).load(function() {
				// call when an image gets loaded
				$('.showcase-featured-list').masonry();
			});
		});
	});
</script>