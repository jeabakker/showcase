<?php

if (get_subtype_id('object', 'showcase')) {
	update_subtype('object', 'showcase', 'ElggShowcase');
} else {
	add_subtype('object', 'showcase', 'ElggShowcase');
}


if (get_subtype_id('object', 'showcaseimg')) {
	update_subtype('object', 'showcaseimg', 'ElggShowcaseImg');
} else {
	add_subtype('object', 'showcaseimg', 'ElggShowcaseImg');
}