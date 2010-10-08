jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
	$('#first_name').focus();

	$('a.pop').click(function(){
        window.open(this.href);
        return false;
    });

	// TODO: Changes prices on load if needed (i.e., form validation failed)

	// Show approriates prices
	$('#discount1').click(function()
	{
		$('.price_early').addClass('no_show');
		$('.price_early_discount2').addClass('no_show');
		$('.price_early_discount1').removeClass('no_show');

		$('.price_prereg').addClass('no_show');
		$('.price_prereg_discount2').addClass('no_show');
		$('.price_prereg_discount1').removeClass('no_show');

		$('.price_door').addClass('no_show');
		$('.price_door_discount2').addClass('no_show');
		$('.price_door_discount1').removeClass('no_show');		
	});
	
	// Show approriates prices
	$('#discount2').click(function()
	{
		$('.price_early').addClass('no_show');
		$('.price_early_discount1').addClass('no_show');
		$('.price_early_discount2').removeClass('no_show');
		
		$('.price_prereg').addClass('no_show');
		$('.price_prereg_discount1').addClass('no_show');
		$('.price_prereg_discount2').removeClass('no_show');				

		$('.price_door').addClass('no_show');
		$('.price_door_discount1').addClass('no_show');
		$('.price_door_discount2').removeClass('no_show');
	});
	
	// Show approriates prices
	$('#discount0').click(function()
	{
		$('.price_early').removeClass('no_show');
		$('.price_early_discount1').addClass('no_show');
		$('.price_early_discount2').addClass('no_show');
		
		$('.price_prereg').removeClass('no_show');
		$('.price_prereg_discount1').addClass('no_show');
		$('.price_prereg_discount2').addClass('no_show');
		
		$('.price_door').removeClass('no_show');
		$('.price_door_discount1').addClass('no_show');
		$('.price_door_discount2').addClass('no_show');
	});
	
	// Show fields when needed (for initial page load)
	if ($('#housing_provider').attr('checked'))
		$('#housing_provider_fields').removeClass('no_show');
	
	// Show fields when needed
	$('#housing_provider').click(function()
	{
		if ($('#housing_provider').attr('checked'))
			$('#housing_provider_fields').removeClass('no_show');
		else
			$('#housing_provider_fields').addClass('no_show');
	});

	// Show fields when needed (for initial page load)
	if ($('#housing_needed').attr('checked'))
		$('#housing_needed_fields').removeClass('no_show');

	// Show fields when needed
	$('#housing_needed').click(function()
	{
		if ($('#housing_needed').attr('checked'))
			$('#housing_needed_fields').removeClass('no_show');
		else
			$('#housing_needed_fields').addClass('no_show');
	});
});
