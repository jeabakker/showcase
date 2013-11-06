<?php

$showcase = $vars['entity'];

$icon = elgg_view_entity_icon($showcase, 'master');

$body = elgg_view('output/longtext', array('value' => $showcase->description));

echo elgg_view_image_block($icon, $body);