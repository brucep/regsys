jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
	$('#first_name').focus();

	$('a.pop').click(function(){
        window.open(this.href);
        return false;
    });

	if (!$('#housing_type_provider').attr('checked')) {
		$('#housing_type_provider_fields').addClass('no_show');
	}

	if (!$('#housing_type_needed').attr('checked')) {
		$('#housing_type_needed_fields').addClass('no_show');
	}

	$('#housing_type_provider').click(function()
	{
		if ($('#housing_type_provider').attr('checked')) {
			$('#housing_type_provider_fields').removeClass('no_show');
		}
		else {
			$('#housing_type_provider_fields').addClass('no_show');
		}
	});

	$('#housing_type_needed').click(function()
	{
		if ($('#housing_type_needed').attr('checked')) {
			$('#housing_type_needed_fields').removeClass('no_show');
		}
		else {
			$('#housing_type_needed_fields').addClass('no_show');
		}
	});
});
