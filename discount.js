$(document).ready(function() {
	// Watch the bulk actions dropdown, looking for custom bulk actions
  	$("#bulk-action-selector-top, #bulk-action-selector-bottom").on('change', function(e){
  		var $this = $(this);

  		if ( $this.val() == 'de_set_product_sale_price' ) {
			$this.after($("<input>", { type: 'text', placeholder: "End Date", name: "de_bulk_product_discount_end" }).addClass("de-custom-bulk-actions-elements datepicker"));
			$this.after($("<input>", { type: 'text', placeholder: "Start Date", name: "de_bulk_product_discount_start" }).addClass("de-custom-bulk-actions-elements datepicker"));		
			$this.after($("<input>", { type: 'text', placeholder: "Percentage", name: "de_bulk_product_discount_percent" }).addClass("de-custom-bulk-actions-elements"));	
			$('.datepicker').datepicker();		
  		} else {
  			$(".de-custom-bulk-actions-elements").remove();
  		}
	  }); 	  
	
});