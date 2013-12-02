//<script>
	
$(document).ready(function() {
	
	// add another screenshot field
	$('.showcase-add-another').click(function(e) {
		e.preventDefault();
		
		// make sure we don't give more than 8 uploads
		var existing_screenshots = $('ul.elgg-showcase-screenshots li').length;
		var available_fields = $('.showcase-screenshot-input table').length;
		var total_screenshots = existing_screenshots + available_fields;
		
		if (total_screenshots >= 9) {
			elgg.register_error(elgg.echo('showcase:screenshot:limit'));
			return;
		}
		
		$('#showcase-screenshot-wrapper').clone(true)
			.removeAttr('id')
			.insertBefore($(this))
			.find('td.remove')
			.html('<a href="#"><span class="elgg-icon elgg-icon-delete"></span></a>');
	});
	
	
	// remove screenshot field
	$('td.remove a .elgg-icon-delete').live('click', function(e) {
		e.preventDefault();
		
		$(this).parents('table').eq(0).remove();
	});
	
	
	// delete a screenshot
	$('.elgg-showcase-screenshot-delete').live('click', function(e) {
		e.preventDefault();
		
		var container = $(this).parents('li').eq(0);
		
		// hide it initially
		container.hide();
		
		elgg.action('showcase/screenshot/delete', {
			timeout: 30000,
			data: {
				guid: $(this).attr('data-guid')
			},
			success: function(result, success, xhr){
                if (result.status == 0) {
					// successfully removed it, remove the markup
					container.remove();
					
					// if we have less than 9 screenshots remaining we can show the field if hidden
					if ($('ul.elgg-showcase-screenshots li').length < 9) {
						$('.showcase-screenshot-input').show();
					}
                }
                else {
					// it didn't delete properly, show it again
					container.show();
					elgg.register_error(elgg.echo('showcase:imagedelete:failed'));
                }
			},
			error: function(result, response, xhr) {
				container.show();
				elgg.register_error(elgg.echo('showcase:imagedelete:failed'));
			}
        });
	});
});