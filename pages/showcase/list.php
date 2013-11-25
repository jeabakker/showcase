<?php

$filter = get_input('filter', 'featured');

// set defaults
$count_getter = 'elgg_get_entities';
$list_getter = 'elgg_list_entities';
$options = array(
    'type' => 'object',
    'subtype' => 'showcase',
	'full_view' => false,
    'count' => true
);


switch ($filter) {
	case 'featured':
		$count_getter = 'elgg_get_entities_from_metadata';
		$list_getter = 'elgg_list_entities_from_metadata';
		$options['metadata_name_value_pairs'] = array(
			'name' => 'featured',
			'value' => '1'
		);
		break;
	case 'mine':
		if (!elgg_is_logged_in()) {
			// only available to logged in users
			break;
		}
		
		$options['owner_guid'] = elgg_get_logged_in_user_guid();
		break;
	case 'unvalidated':
		if (!elgg_is_admin_logged_in()) {
			break;
		}
		
		$options['wheres'] = array("e.access_id = " . ACCESS_PRIVATE);
		break;
	case 'all':
	default:
		// defaults already set
		break;
}

$title = elgg_echo('showcase');

$count = $count_getter($options);

if ($count) {
    unset($options['count']);
    $content = $list_getter($options);
}
else {
    $content = elgg_echo('showcase:noresults');
}

$layout = elgg_view_layout('content', array(
	'title' => $title,
    'content' => $content,
	'filter' => elgg_view('showcase/filter'),
	'sidebar' => elgg_view('showcase/sidebar')
));

echo elgg_view_page(elgg_echo('showcase'), $layout);