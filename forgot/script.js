var scopeDetails = [];

$( document ).ready(function() {
	$('#token').keyup(function(e){
		if(e.keyCode == 13)
			onQuery();
	});
    getScopes();
});

function getScopes() {
	$.get("https://twitchtokengenerator.com/api/scopes", function(data){
		$.each(data['v5'], function(id, scope) {
			scopeDetails.push([scope['scope'], scope['desc']]);
		});
		$.each(data['helix'], function(id, scope) {
			scopeDetails.push([scope['scope'], scope['desc']]);
		});
	});
}

function onQuery() {
	var token = $('#token').val();
	query(token);
}

function query(token) {
	$.get("https://twitchtokengenerator.com/api/forgot/" + token, function(data){
        switch(data['result']) {
			case "invalid":
				alert("The token you submitted does not appear to be a valid token.");
				break;
			case "expired":
				alert("The token you submitted appears to have expired/revoked.");
				break;
			case "success":
				populatePage(data['data']);
				break;
			default:
				alert("Unknown response received from server.");
				break;
		}
    });
}

function populatePage(data) {
	$('#username').val(data['username']);
	$('#userid').val(data['userid']);
	$('#created_at').val(data['created_at']);
	$('#updated_at').val(data['updated_at']);
	
	$('#applicable_scopes tr').remove();
	
	$.each(data['scopes'], function(id, scope) {
		$.each(scopeDetails, function(id, storedScope) {
			if(scope == storedScope[0]) {
				$('<tr><td class="text-center"><code>' + scope +'</code></td><td class="text-center">' + storedScope[1] + '</td></tr>').appendTo('#applicable_scopes');
			}
		});
	});
}