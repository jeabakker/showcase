<?php
elgg_load_js('lightbox');
elgg_load_css('lightbox');
elgg_load_js('showcase');

$showcase = $vars['entity'];
$images = array();
$gallery = '';

if ($showcase->guid) {
	echo elgg_view('output/longtext', array(
		'value' => elgg_echo('showcase:edit:review'),
		'class' => 'elgg-subtext showcase-review-notice'
	));
	
	$images = elgg_get_entities_from_relationship(array(
		'type' => 'object',
		'subtype' => 'showcaseimg',
		'relationship' => 'screenshot',
		'relationship_guid' => $showcase->guid,
		'inverse_relationship' => true,
		'limit' => 10,
		'order_by' => 'e.time_created ASC'
	));
	
	$imgcount = count($images);
	
	$gallery = '<ul class="elgg-gallery elgg-showcase-screenshots">';
	foreach ($images as $img) {
		$thumb_url = elgg_get_site_url() . "showcase/icon/{$img->guid}/medium/" . md5($img->time_created) . '.jpg';
		$full_url = elgg_get_site_url() . "showcase/icon/{$img->guid}/master/" . md5($img->time_created) . '.jpg';
		$gallery .= '<li>';
		$gallery .= "<a class=\"elgg-showcase-screenshot elgg-lightbox\" href=\"$full_url\" rel=\"showcase-gallery\"><img src=\"$thumb_url\" alt=\"$img->title\" title=\"$img->title\"/></a>";
		$gallery .= elgg_view('output/url', array(
			'text' => elgg_view_icon('delete'),
			'href' => 'action/showcase/screenshot/delete?guid=' . $img->guid,
			'is_action' => true,
			'is_trusted' => true,
			'data-guid' => $img->guid,
			'class' => 'elgg-showcase-screenshot-delete'
		));
		$gallery .= '</li>';
	}
	$gallery .= '</ul>';
}

$address_value = elgg_get_sticky_value('showcase', 'address');
if (!$address_value) {
    $address_value = $showcase->address;
}
$address = array(
	'name' => 'address',
	'id' => 'showcase_address',
	'value' => $address_value,
);

$title_value = elgg_get_sticky_value('showcase', 'title');
if (!$title_value) {
    $title_value = $showcase->title;
}
$title = array(
	'name' => 'title',
	'id' => 'showcase_title',
	'value' => $title_value,
);


$description_value = elgg_get_sticky_value('showcase', 'description');
if (!$description_value) {
    $description_value = $showcase->description;
}
$description = array(
	'name' => 'description',
	'id' => 'showcase_description',
	'value' => $description_value,
);

$tags_value = elgg_get_sticky_value('showcase', 'tags');
if (!$tags_value) {
    $tags_value = $showcase->tags;
}
$tags = array(
	'name' => 'tags',
	'id' => 'showcase_tags',
	'value' => $tags_value,
);

$categories_value = elgg_get_sticky_value('showcase', 'categories');
if (!$categories_value) {
    $categories_value = $showcase->categories;
}
$categories = array(
	'name' => 'categories',
	'id' => 'showcase_categories',
	'value' => $categories_value,
);

?>

<div>
	<label for="showcase_screenshot"><?php echo elgg_echo('showcase:screenshot'); ?></label>
	<?php
		$screenshot_class = 'showcase-screenshot-input';
		if ($imgcount >= 9) {
			$screenshot_class .= ' hidden';
		}
	
		echo '<div class="' . $screenshot_class . '">';
		echo '<table id="showcase-screenshot-wrapper"><tr><td>';
        echo elgg_view('input/file', array('name' => 'screenshot[]'));
		echo '</td><td class="remove"></td></tr></table>';
		echo elgg_view('output/url', array(
			'text' => elgg_echo('showcase:add:another'),
			'href' => '#',
			'class' => 'showcase-add-another elgg-button elgg-button-action'
		));
		echo '</div>';
        echo elgg_view('output/longtext', array(
            'value' => elgg_echo('showcase:screenshot:help'),
            'class' => 'elgg-subtext'
        ));
		
		if ($gallery) {
			echo '<br>' . $gallery;
		}
        ?>
</div>
<div>
	<label for="showcase_address"><?php echo elgg_echo('showcase:address'); ?></label>
	<?php echo elgg_view('input/url', $address); ?>
</div>
<div>
	<label for="showcase_title"><?php echo elgg_echo('showcase:title'); ?></label>
	<?php echo elgg_view('input/text', $title); ?>
</div>
<div>
	<label for="showcase_description"><?php echo elgg_echo('showcase:description'); ?></label>
	<?php
		echo elgg_view('input/longtext', $description);
		echo elgg_view('output/longtext', array(
			'value' => elgg_echo('showcase:description:help'),
			'class' => 'elgg-subtext'
		));
	?>
	
</div>
<div>
	<label for="showcase_tags"><?php echo elgg_echo('tags'); ?></label>
	<?php echo elgg_view('input/tags', $tags); ?>
</div>

<?php
    if (elgg_is_active_plugin('categories')) {
?>
<div>
    <?php echo elgg_view('input/categories', $categories); ?>
</div>
<?php
    }
?>

<div>
	<?php
		$comment_opts = array(
			'name' => 'allow_comments',
			'value' => 1,
		);
		
		if ($showcase->allow_comments !== '0') {
			$comment_opts['checked'] = 'checked';
		}
		
		echo elgg_view('input/checkbox', $comment_opts);
	?>
	<label for="showcase_comments"><?php echo elgg_echo('showcase:allow:comments'); ?></label>
</div>

<div class="elgg-foot">
<?php
	echo elgg_view('input/hidden', array('name' => 'container_guid', 'value' => elgg_get_page_owner_guid()));
	echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $showcase->guid));
	echo elgg_view('input/submit', array('value' => elgg_echo('submit')));
    
    elgg_clear_sticky_form('showcase');
?>
</div>
