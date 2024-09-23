jQuery(document).ready(function($){
	$('.emd-enable-pagination').click(function(){
		$(this).closest('.widget-content').find('.emd-paginate-show').toggle();
	});
	$(document).on({ 'widget-added widget-updated': function (e, widget){ 
		$('.emd-enable-pagination').click(function(){
			$(this).closest('.widget-content').find('.emd-paginate-show').toggle();
		});
	}
	});
});
