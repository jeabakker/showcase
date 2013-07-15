<?php

$options = array(
    'type' => 'object',
    'subtype' => 'showcase',
    'metadata_name_value_pairs' => array('name' => 'pending', 'value' => 1),
    'count' => true
);

$count = elgg_get_entities_from_metadata($options);

if ($count) {
    unset($options['count']);
    echo elgg_list_entities_from_metadata($options);
}
else {
    echo elgg_echo('showcase:noresults');
}