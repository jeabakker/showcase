<?php

$showcase = $vars['entity'];

$screenshot = array(
	'name' => 'screenshot',
	'id' => 'showcase_screenshot',
);

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
        echo elgg_view('input/file', $screenshot);
        echo elgg_view('output/longtext', array(
            'value' => elgg_echo('showcase:screenshot:help'),
            'class' => 'elgg-subtext'
        ));
        ?>
</div>
<div>
	<label for="showcase_address"><?php echo elgg_echo('showcase:address'); ?></label>
	<?php echo elgg_view('input/url', $address); ?>
</div>
<div>
	<label for="showcase_title"><?php echo elgg_echo('title'); ?></label>
	<?php echo elgg_view('input/text', $title); ?>
</div>
<div>
	<label for="showcase_description"><?php echo elgg_echo('description'); ?></label>
	<?php echo elgg_view('input/longtext', $description); ?>
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

<div class="elgg-foot">
<?php
	echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $showcase->guid));
	echo elgg_view('input/submit', array('value' => elgg_echo('submit')));
    
    elgg_clear_sticky_form('showcase');
?>
</div>
