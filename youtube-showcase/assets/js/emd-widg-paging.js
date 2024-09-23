jQuery(document).ready(function($){
	widgpage ={};
	$.fn.widgPagination = function(page_click){
		var div_id = $(page_click).closest('.emd-widg-results').attr('id');
		var other_divs = [];
		other_divs.push(div_id);
		other_divs.forEach(function(div_id) {
			if(widgpage[div_id] == undefined){
				widgpage[div_id] = 1;	
			}
			if($(page_click).hasClass('prev')){
				widgpage[div_id] --;
			}  
			else if($(page_click).hasClass('next')){
				widgpage[div_id] ++;
			}  
			else{  
				widgpage[div_id] = $(page_click).text();
			}
			var app = $('#'+div_id).find('#emd_app').val();
			load_posts(div_id,app);
		});
	}
	$('.widg-pagination a').click(function(e){
		$(this).widgPagination($(this));
		return false;
	}); 
	var load_posts = function(div_id,app){
		$.ajax({
			type: 'GET',
			url: emd_widg_paging_vars.ajax_url,
			cache: false,
			async: false,
			data: {action:'emd_get_widg_pagenum',pageno: widgpage[div_id],div_id:div_id,app:app},
			success: function(response){
				$('#'+ div_id).html(response);
				$('#'+div_id+' .widg-pagination a').click(function(){
					$(this).widgPagination($(this));
					return false;
				});
			},
		});
	}
});
