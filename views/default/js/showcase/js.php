//<script>
	
$(document).ready(function() {
	
	// add another screenshot field
	$('.showcase-add-another').click(function(e) {
		e.preventDefault();
		
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