<?php
class ElggShowcaseImg extends ElggFile {
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['subtype'] = 'showcaseimg';
		$this->attributes['access_id'] = ACCESS_PRIVATE;
	}
}