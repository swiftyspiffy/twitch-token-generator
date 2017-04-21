var client_id = "gp762nuuoqcoxypju8c569th9wz7q5";
var redirect_uri = "https://twitchtokengenerator.com";

var scopes = ["user_read", "user_blocks_edit", "user_blocks_read", "user_follows_edit", "channel_read", "channel_editor", "channel_commercial", "channel_stream", "channel_subscriptions", "user_subscriptions", "channel_check_subscription", "chat_login", "channel_feed_read", "channel_feed_edit", "collections_edit", "communities_edit", "communities_moderate", "viewing_activity_read"];


$( document ).ready(function() {
    console.log( "loaded! enabling custom checkboxes!" );
	$(':checkbox').checkboxpicker();
	console.log( "checking and selecting checkboxes that are in querystring." );
	applyScopeParam();
});

function applyScopeParam() {
	var params = getUrlVars();
	if(params['scope'] != undefined 
	  && params['scope'].includes("+")
	  && authSuccessful) {
		var activeScopes = params['scope'].split("+");
		activeScopes.forEach(function(activeScope) {
			$('#check_' + activeScope).prop('checked', true);
		});
	}
}

function clearScopeSelections() {
	scopes.forEach(function(scope) {
		$('#check_' + scope).prop('checked', false);
	});
}

function authenticate() {
	// get user selected scopes
	var selectedScopes = gatherScopeSelections();
	// check at least one scope is set
	if(selectedScopes.length == 0) {
		alert("You need to select at least one scope to generate a token.");
		return;
	}
	// build string to auth with twitch
	var scopeString = "";
	selectedScopes.forEach(function(scope) {
		if(scopeString == "")
			scopeString = scope;
		else
			scopeString += "+" + scope;
	});
	// redirect to twitch auth
	window.location = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=" + client_id + "&redirect_uri=" + redirect_uri + "&scope=" + scopeString;
}

function gatherScopeSelections() {
	var selectedScopes = [];
	scopes.forEach(function(scope) {
		if($('#check_' + scope).is(':checked'))
			selectedScopes.push(scope);
	});
	return selectedScopes;
}

// Source: http://stackoverflow.com/a/4656873
function getUrlVars()
{
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++)
    {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}