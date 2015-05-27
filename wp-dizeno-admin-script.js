jQuery( document ).ready(function() {

	// script for posttype create and edit
  	if(jQuery('#dizeno_category_li input[type=checkbox]').prop( "checked" )) {
		jQuery('#dizeno_category_li > div').addClass('show');
  		jQuery('#dizeno_category_li > div').removeClass('hide');
	} else {
		jQuery('#dizeno_category_li > div').removeClass('show');
  		jQuery('#dizeno_category_li > div').addClass('hide');
	}
	if(jQuery('#dizeno_tag_li input[type=checkbox]').prop( "checked" )) {
		jQuery('#dizeno_tag_li > div').addClass('show');
  		jQuery('#dizeno_tag_li > div').removeClass('hide');
	} else {
		jQuery('#dizeno_tag_li > div').removeClass('show');
  		jQuery('#dizeno_tag_li > div').addClass('hide');
	}
  	jQuery('#dizeno_category_li input[type=checkbox]').on('click', function() {
  		if(jQuery('#dizeno_category_li input[type=checkbox]').prop( "checked" )) {
  			jQuery('#dizeno_category_li > div').addClass('show');
  			jQuery('#dizeno_category_li > div').removeClass('hide');
  		} else {
  			jQuery('#dizeno_category_li > div').removeClass('show');
  			jQuery('#dizeno_category_li > div').addClass('hide');
  		}
  	});
  	jQuery('#dizeno_tag_li input[type=checkbox]').on('click', function() {
	  	if(jQuery('#dizeno_tag_li input[type=checkbox]').prop( "checked" )) {
			jQuery('#dizeno_tag_li > div').addClass('show');
	  		jQuery('#dizeno_tag_li > div').removeClass('hide');
		} else {
			jQuery('#dizeno_tag_li > div').removeClass('show');
	  		jQuery('#dizeno_tag_li > div').addClass('hide');
		}
	});
});
