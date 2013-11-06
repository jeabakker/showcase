<?php

$options = array(
    'type' => 'object',
    'subtype' => 'showcase',
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