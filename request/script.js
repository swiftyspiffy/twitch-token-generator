$( document ).ready(function() {
    console.log( "loaded! enabling custom checkboxes!" );
	$(':checkbox').checkboxpicker();
	console.log( "checking and selecting checkboxes that are in querystring." );
});