<?php
//elgg_load_js('showcase/imagesloaded');
elgg_load_js('showcase/masonry');

$showcase = $vars['entity'];
$owner = $showcase->getOwnerEntity();

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