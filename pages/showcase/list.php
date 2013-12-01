<?php

$filter = get_input('filter', 'featured');

// set defaults
$title = elgg_echo('showcase');
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
		$title = elgg_echo('showcase:title:featured');
		$count_getter = 'elgg_get_entities_from_metadata';
		$list_getter = 'elgg_list_entities_from_metadata';
		$options['metadata_name_value_pairs'] = array(
			'name' => 'showcase_featured',
			'value' => '1'
		);
		$options['wheres'] = array("e.access_id = " . ACCESS_PUBLIC);
		break;
	case 'owner':
		$owner = get_user(get_input('owner_guid'));
		if (!$owner) {
			break;
		}
		
		$title = elgg_echo('showcase:title:owner', array(elgg_get_logged_in_user_entity()->name));
		$options['owner_guid'] = $owner->guid;
		break;
	case 'friends':
		$owner = get_user(get_input('owner_guid'));
		if (!$owner) {
			break;
		}
		
		$fr_options = array(
			'type' => 'user',
			'relationship' => 'friend',
			'relationship_guid' => $owner->guid,
			'limit' => 0,
			'callback' => false // no need for entities, keep it quick
		);
		
		$friends = new ElggBatch('elgg_get_entities_from_relationship', $fr_options);
		$friend_guids = array();
		foreach ($friends as $friend) {
			$friend_guids[] = $friend->guid;
		}
		
		$title = elgg_echo('showcase:title:friends');
		
		if ($friend_guids) {
			$options['owner_guids'] = $friend_guids;
		}
		else {
			$options['joins'] = false; // invalidate the query, nothing to show
		}
		break;
	case 'unvalidated':
		if (!elgg_is_admin_logged_in()) {
			break;
		}
		
		$title = elgg_echo('showcase:title:unvalidated');
		$options['wheres'] = array("e.access_id = " . ACCESS_PRIVATE);
		
		// don't want to lose updates to older showcases in the noise, bump them back to the top
		$options['order_by'] = 'e.time_updated DESC';
		break;
	case 'all':
	default:
		// defaults already set
		break;
}

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