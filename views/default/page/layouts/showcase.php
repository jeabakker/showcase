<?php
/**
 *  Modified elgg one_column layout to allow for title menu
 *  @TODO - I end up doing this quite a bit - should this be in core?
 */

$class = 'elgg-layout elgg-layout-one-column clearfix';
if (isset($vars['class'])) {
	$class = "$class {$vars['class']}";
}

// navigation defaults to breadcrumbs
$nav = elgg_extract('nav', $vars, elgg_view('navigation/breadcrumbs'));

?>
<div class="<?php echo $class; ?>">
	<div class="elgg-body elgg-main">
	<?php
		echo $nav;

		// allow page handlers to override the default header
        if (isset($vars['header'])) {
            $vars['header_override'] = $vars['header'];
        }
        echo elgg_view('page/layouts/content/header', $vars);

		echo $vars['content'];
		
		// @deprecated 1.8
		if (isset($vars['area1'])) {
			echo $vars['area1'];
		}
	?>
	</div>
</div>