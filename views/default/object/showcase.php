<?php

if (!$vars['entity']) {
	return;
}

if ($vars['full_view']) {
	echo elgg_view('object/showcase/full', $vars);
}
else {
	echo elgg_view('object/showcase/list', $vars);
}