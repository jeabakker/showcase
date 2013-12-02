<?php

$filter = get_input('filter', 'featured');

$tabs = array(
	array(
		'name' => 'featured',
		'href' => 'showcase?filter=featured',
		'text' => elgg_echo('showcase:tab:featured'),
		'selected' => ($filter == 'featured')
	),
	array(
		'name' => 'all',
		'href' => 'showcase?filter=all',
		'text' => elgg_echo('all'),
		'selected' => ($filter == 'all'),
	)
);


if (elgg_is_logged_in()) {
	$mine = array(
		'name' => 'owner',
		'href' => 'showcase/owner/' . urlencode(elgg_get_logged_in_user_entity()->username),
		'text' => elgg_echo('mine'),
		'selected' => ($filter == 'owner')
	);
	
	$tabs[] = $mine;
	
	$friends = array(
		'name' => 'mine',
		'href' => 'showcase/friends/' . urlencode(elgg_get_logged_in_user_entity()->username),
		'text' => elgg_echo('friends'),
		'selected' => ($filter == 'friends')
	);
	
	$tabs[] = $friends;
}


if (elgg_is_admin_logged_in()) {
	$unvalidated = array(
		'name' => 'unvalidated',
		'href' => 'showcase?filter=unvalidated',
		'text' => elgg_echo('showcase:tab:unvalidated'),
		'selected' => ($filter == 'unvalidated')
	);
	
	$tabs[] = $unvalidated;
}


echo elgg_view('navigation/tabs', array('tabs' => $tabs));