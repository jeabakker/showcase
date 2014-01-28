<?php

$filter = get_input('filter', 'featured');

// set defaults
$title = elgg_echo('showcase:list:all');
$content = '';
$about = elgg_view('output/longtext', array(
	'value' => elgg_echo('showcase:about'),
	'class' => 'elgg-subtext'
));
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
		elgg_push_context('showcase_featured');
		$title = elgg_echo('showcase:title:featured');
		$count_getter = 'elgg_get_entities_from_metadata';
		$list_getter = 'elgg_list_entities_from_metadata';
		$options['metadata_name_value_pairs'] = array(
			'name' => 'showcase_featured',
			'value' => '1'
		);
		
		$options['wheres'] = array("e.access_id = " . ACCESS_PUBLIC);
		
		
		// kludgy way to avoid using order by RAND() - which is VERY slow
		// use random offsets and populate an array of guids to get with our getter
		$count = elgg_get_entities_from_metadata($options);
		unset($options['count']);
		
		if ($count) {
			// lets get random entities by using random offset
			$offsets = array();
			while (count($offsets) < min(array($count, 15))) {
				$rand = rand(0, ($count - 1));
				if (in_array($rand, $offsets)) {
					continue;
				}
				
				$offsets[] = $rand;
			}
			
			$entities = array();
			$options['limit'] = 1;
			foreach ($offsets as $o) {
				$options['offset'] = $o;
				$entity = elgg_get_entities_from_metadata($options);
				if ($entity) {
					$entities[] = $entity[0];
				}
			}
		}
		
		$options['pagination'] = false;
		$options['item_class'] = 'showcase-featured-item';
		$options['list_class'] = 'showcase-featured-list';
		
		if ($entities) {
			$content = elgg_view_entity_list($entities, $options);
		}
		else {
			$content = elgg_echo('showcase:noresults');
		}

		break;
	case 'owner':
		$owner = get_user(get_input('owner_guid'));
		if (!$owner) {
			break;
		}
		
		$title = elgg_echo('showcase:title:owner', array($owner->name));
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
		
		$about = '';
		break;
	case 'all':
	default:
		// defaults already set
		$options['wheres'] = array("e.access_id = " . ACCESS_PUBLIC);
		break;
}


if (!$content) {
	$count = $count_getter($options);

	if ($count) {
		unset($options['count']);
		$content = $list_getter($options);
	}
	else {
		$content = elgg_echo('showcase:noresults');
	}
}

$layout = elgg_view_layout('content', array(
	'title' => $title,
    'content' => $about . $content,
	'filter' => elgg_view('showcase/filter'),
	'sidebar' => elgg_view('showcase/sidebar')
));

echo elgg_view_page(elgg_echo('showcase'), $layout);