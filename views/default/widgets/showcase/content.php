<?php

$limit = (int)$vars['entity']->num_results ? (int)$vars['entity']->num_results : 10;

$options = array(
    'type' => 'object',
    'subtype' => 'showcase',
	'owner_guid' => $vars['entity']->owner_guid,
	'limit' => $limit,
	'full_view' => false,
    'count' => true
);

$count = elgg_get_entities($options);

if ($count) {
	unset($options['count']);
	echo elgg_list_entities($options);
}
else {
	echo elgg_echo('showcase:noresults');
}