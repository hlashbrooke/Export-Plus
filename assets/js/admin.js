jQuery(document).ready(function($){
	var form = $('#export-filters');
	form.find('input:checkbox').change(function() {
		if( 'all' != $(this).val() ) {
			var checked = $(this).attr('checked');
			if('checked' != checked) {
				$('.selectall').removeAttr('checked');
			}
			switch ( $(this).val() ) {
				case 'posts':
					if('checked' != checked) {
						$('#post-filters').slideUp( 'fast' );
					} else {
						$('#post-filters').slideDown( 'fast' );
					}
				break;
			}
		}
	});
		$('.selectall' ).click(function() {
			var checked = this.checked;
	    form.find('input:checkbox').each(function() {
	    	$(this).attr('checked',checked);
			$(this).change();
 		});
	});
});