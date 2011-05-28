jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
	$('#first_name').focus();

	$('a.pop').click(function(){
        window.open(this.href);
        return false;
    });

	// Show appropriate prices
	$('#payment_discount').click(function()
	{
		if ($('#payment_discount').attr('checked')) {
			$('#packages .price_prereg').addClass('no_show');
			$('#packages .price_prereg_discount').removeClass('no_show');
		}
		else {
			$('#packages .price_prereg').removeClass('no_show');
			$('#packages .price_prereg_discount').addClass('no_show');
		}
	});
	
	// Show fields when needed (for initial page load)
	if ($('#housing_type_provider').attr('checked'))
		$('#housing_type_provider_fields').removeClass('no_show');
	
	// Show fields when needed
	$('#housing_type_provider').click(function()
	{
		if ($('#housing_type_provider').attr('checked'))
			$('#housing_type_provider_fields').removeClass('no_show');
		else
			$('#housing_type_provider_fields').addClass('no_show');
	});

	// Show fields when needed (for initial page load)
	if ($('#housing_type_needed').attr('checked'))
		$('#housing_type_needed_fields').removeClass('no_show');

	// Show fields when needed
	$('#housing_type_needed').click(function()
	{
		if ($('#housing_type_needed').attr('checked'))
			$('#housing_type_needed_fields').removeClass('no_show');
		else
			$('#housing_type_needed_fields').addClass('no_show');
	});
});
