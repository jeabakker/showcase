<?php

echo elgg_echo('showcase:widget:label:number') . '&nbsp;';
echo elgg_view('input/dropdown', array(
	'name' => 'params[num_results]',
	'value' => $vars['entity']->num_results ? $vars['entity']->num_results : 10,
	'options_values' => array(
		1,2,3,4,5,6,7,8,9,10,15,20
	)
));

echo '<br><br>';