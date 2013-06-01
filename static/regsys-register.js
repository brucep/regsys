jQuery(document).ready(function($) {
    // $() will work as an alias for jQuery() inside of this function
	$('a.pop').click(function(){
        window.open(this.href);
        return false;
    });

	if (!$('#housingTypeProvider').prop('checked')) {
		$('#housingTypeProviderFields').addClass('hidden');
	}

	if (!$('#housingTypeNeeded').prop('checked')) {
		$('#housingTypeNeededFields').addClass('hidden');
	}

	$('#housingTypeProvider').click(function()
	{
		if ($('#housingTypeProvider').prop('checked')) {
			$('#housingTypeProviderFields').removeClass('hidden');
		}
		else {
			$('#housingTypeProviderFields').addClass('hidden');
		}
	});

	$('#housingTypeNeeded').click(function()
	{
		if ($('#housingTypeNeeded').prop('checked')) {
			$('#housingTypeNeededFields').removeClass('hidden');
		}
		else {
			$('#housingTypeNeededFields').addClass('hidden');
		}
	});
});
