var auth_base = "https://id.twitch.tv/oauth2";
var client_id = "gp762nuuoqcoxypju8c569th9wz7q5";
var redirect_uri = "https://twitchtokengenerator.com";

var scopes = getScopes();
var waitingRotator;
var waitingTexts = [];

$( document ).ready(function() {
    console.log( "loaded! enabling custom checkboxes!" );
	$(':checkbox').checkboxpicker();
	console.log( "checking and selecting checkboxes that are in querystring." );
	if(window.location.hash) {
		var requestUrl = "https://twitchtokengenerator.com/request/" + window.location.hash.replace("#", "") + "/" + token;
		window.location.href = requestUrl;
	} else {
		applyScopeParam();
		var vars = getUrlVars();
		if(vars['auth'] != null && vars['auth'] != "") {
			if(vars['auth'] == "auth_auth") {
				authenticate();
			}
		}
	}
	
	getWaitingTexts();
	waitingRotator = setInterval(rotateWaitingText, 3000);
	
	if(!authSuccessful && !scopes_set) {
	    launchWelcomeModal();
    }
	
	if(captchaId != "") {
		showRecaptchaModal(captchaId);
	}
	
	$('#robot_form').on('submit', function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr("action") || window.location.pathname,
			type: "POST",
			data: $(this).serialize(),
			success: function(data) {
				if(data.success) {
					setAccessText(data['result']['access']);
					setRefreshText(data['result']['refresh']);
					setSuccessStyle();
				} else {
					setAccessText("ERROR: Unable to ensure you're not a robot!");
					setRefreshText("ERROR: Unable to ensure you're not a robot!");
					setErrorStyle();
					alert("Error checking robot status! Details below:\n\n" + data.message);
				}
				$('#cyborgModal').modal("hide");
			},
			error: function(data) {
				setAccessText("ERROR: Unable to ensure you're not a robot!");
				setRefreshText("ERROR: Unable to ensure you're not a robot!");
				setErrorStyle();
				alert("Internal error. Please contact swiftyspiffy.");
			}
		});
	});
	
	$('#wrong-account').click(function() {
		alert("Is this not your bot account? Don't worry! Generate tokens for a different account by:\n1. Go to twitch.tv\n2. Logout of the current account\n3. Login to the account you want to generate a token for\n4. Come back to twitchtokengenerator.com\n5. Generate token!\n\nProtip: Bot accounts are actually just Twitch accounts. Register a Twitch account for your bot, and login to it to generate a token for it.");
	});
});

function setAccessText(val) {
	$('#access').val(val);
}
function setRefreshText(val) {
	$('#refresh').val(val);
}
function setSuccessStyle() {
	$('#access').attr("style", "text-align: center; font-size: 200%; color: #009900;");
	$('#refresh').attr("style", "text-align: center; font-size: 200%; color: #009900;");
}
function setErrorStyle() {
	$('#access').attr("style", "text-align: center; font-size: 200%; color: #a31824;");
	$('#refresh').attr("style", "text-align: center; font-size: 200%; color: #a31824;");
}

var quickLinkToggleType = "auth_stay";
function toggleQuickLinkAuth(id) {
	if(id == "quicklink_auth_stay") {
		$('#quicklink_auth_stay').addClass("btn-primary").removeClass("btn-secondary");
		$('#quicklink_auth_auth').addClass("btn-secondary").removeClass("btn-primary");
		quickLinkToggleType = "auth_stay";
	} else {
		$('#quicklink_auth_stay').addClass("btn-secondary").removeClass("btn-primary");
		$('#quicklink_auth_auth').addClass("btn-primary").removeClass("btn-secondary");
		quickLinkToggleType = "auth_auth";
	}
}

function getWaitingTexts() {
	var resp = JSON.parse($.ajax({
		type: "GET",
		url: "https://twitchtokengenerator.com/api/waiting_texts",
		async: false
	}).responseText);
	waitingTexts = resp.texts;
}

function rotateWaitingText() {
	var t = waitingTexts[Math.floor(Math.random()*waitingTexts.length)];
	$('#waiting_text').html(t);
}

function getScopes() {
	return JSON.parse($.ajax({
		type: "GET",
		url: "https://twitchtokengenerator.com/api/scopes/just_scopes",
		async: false
	}).responseText);
}

function addButtonMetrics(buttonId) {
	$.ajax({
		type: 'GET',
		url: 'https://twitchtokengenerator.com/metrics.php?security_code=' + securityCode + '&action=button&id=' + buttonId,
		async: true
	})
}

function launchRequestModal() {
	$('#requestModal').modal("show");
}

function launchWelcomeModal() {
    $('#welcomeModal').modal("show");
}

var quick_link_scopes;
function launchQuickLinkModal() {
	$('#quick_link_permissions').empty();
	
	quick_link_scopes = gatherScopeSelectionsAlt();
	quick_link_scopes.forEach(function(scope) {
		$('#quick_link_permissions').append('<li><b>' + scope[1] + '</b></li>');
	});
	$('#quickLinkModal').modal('show');
}

function fetchQuickLinkUrl() {
	var scopeString = "";
	quick_link_scopes.forEach(function(scope) {
		if(scopeString == "")
			scopeString = scope[0];
		else
			scopeString += "+" + scope[0];
	});
	
	$.ajax({
		url: "https://twitchtokengenerator.com/quick/create/" + scopeString + "/" + quickLinkToggleType,
		method: "POST",
		dataType: "json",
		context: document.body
	}).done(function(data) {
		if(data.success) {
			$('#quick_generate_link').replaceWith('<div class="input-group"><input type="text" class="form-control" id="quick_generate_link" style="text-align: center; font-size: 120%;" readonly=""><span class="input-group-btn"><button class="btn btn-success" type="button" onclick="copyInput(this, \'quick_generate_link\');">Copy</button></span></div>');
			$("#quick_generate_link").val(data.message);
		} else {
			alert(data.message);
		}
	}).error(function(data) {
		console.log("ERROR: " + data);
	});
}

function fetchRequestUrl() {
	var name = $('#my_name').val();
	var email = $('#my_email').val();
	var scopes = gatherScopeSelections();
	var scopeString = "";
	scopes.forEach(function(scope) {
		if(scopeString == "")
			scopeString = scope;
		else
			scopeString += "+" + scope;
	});
	$.ajax({
		url: "https://twitchtokengenerator.com/request/create.php",
		method: "POST",
		data: { scopes: scopeString, my_name: name, my_email: email },
		dataType: "json",
		context: document.body
	}).done(function(data) {
		if(data.success) {
			$('#generate_link').replaceWith('<input type="text" class="form-control col-md-10 pull-left" id="generated_link" style="padding-left: 15px; padding-right: 15px;" readonly>')
			$("#generated_link").val(data.message);
		} else {
			alert(data.message);
		}
	}).error(function(data) {
		console.log("ERROR: " + data);
	});
}

function applyScopeParam() {
	var params = getUrlVars();
	if(params['scope'] != undefined) {
		if(params['scope'].includes("+")) {
			var activeScopes = params['scope'].split("+");
			activeScopes.forEach(function(activeScope) {
				if(activeScope.includes(":") || activeScope.includes("%3A")) {
					$('#check_helix_' + activeScope.replaceAll(":", "_").replaceAll("%3A", "_")).prop('checked', true);
				} else {
					$('#check_' + activeScope.replaceAll(":", "_")).prop('checked', true);
				}
			});
		} else {
			if(params['scope'].includes(":") || params['scope'].includes("%3A")) {
				$('#check_helix_' + params['scope'].replaceAll(":", "_").replaceAll("%3A", "_")).prop('checked', true);
			} else {
				$('#check_' + params['scope'].replaceAll(":", "_")).prop('checked', true);
			}
		}
	}
}

function selectAllScopes() {
	scopes.forEach(function(scope) {
		if(scope.includes(":")) {
			$('#check_helix_' + scope.replaceAll(":", "_")).prop('checked', true);
		} else {
			$('#check_' + scope).prop('checked', true);
		}
	});
	addButtonMetrics("select-all");
}

function clearScopeSelections() {
	scopes.forEach(function(scope) {
		if(scope.includes(":")) {
			$('#check_helix_' + scope.replaceAll(":", "_")).prop('checked', false);
		} else {
			$('#check_' + scope).prop('checked', false);
		}
	});
	addButtonMetrics("clear-all");
}

function authenticate(force_verify = false) {
	// get user selected scopes
	var selectedScopes = gatherScopeSelections();
	// check at least one scope is set
	/*
	if(selectedScopes.length == 0) {
		alert("You need to select at least one scope to generate a token.");
		return;
	}
	*/ 
	// build string to auth with twitch
	var scopeString = "";
	selectedScopes.forEach(function(scope) {
		if(scopeString == "")
			scopeString = scope;
		else
			scopeString += "+" + scope;
	});
	// redirect to twitch auth
	window.location = auth_base + "/authorize?response_type=code&client_id=" + client_id + "&redirect_uri=" + redirect_uri + "&scope=" + scopeString + "&state=frontend|" + securityCode + "&force_verify=" + (force_verify ? "true" : "false");
}

function gatherScopeSelections() {
	var selectedScopes = [];
	scopes.forEach(function(scope) {
		if($('#check_' + scope).is(':checked')) {
			selectedScopes.push(scope);
		}
		if($('#check_helix_' + scope.replaceAll(":", "_")).is(':checked')) {
			selectedScopes.push(scope);
		}
	});
	return selectedScopes;
}

function gatherScopeSelectionsAlt() {
	var selectedScopes = [];
	scopes.forEach(function(scope) {
		if($('#check_' + scope).is(':checked')) {
			selectedScopes.push([$('#check_' + scope).attr('alt'), scope]);
		}
		if($('#check_helix_' + scope.replaceAll(":", "_")).is(':checked')) {
			selectedScopes.push([$('#check_helix_' + scope.replaceAll(":", "_")).attr('alt'), scope]);
		}
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

function wantsBotToken() {
    $('#check_chat_login').prop('checked', true);
    authenticate(true);
}

function performRefreshRequest() {
	var refresh_token = $('#refresh_token_refresh').val();
	$.ajax({
		url: "https://twitchtokengenerator.com/api/refresh/" + refresh_token,
		dataType: "json",
		context: document.body
	}).done(function(data) {
		if(data.success) {
			$('#refresh_token_refresh').val("");
			$('#access').attr('style', "text-align: center; font-size: 200%; color: #009900;");
			$('#refresh').attr('style', "text-align: center; font-size: 200%; color: #009900;"); 
			$('#access').val(data.token);
			$('#refresh').val(data.refresh);
			$("html, body").animate({ scrollTop: 0 }, 500);
		} else {
			switch(data.error) {
				case 22:
					alert("Invalid refresh token provided!");
					break;
				default:
					alert("Unknown error occurred!");
					break;
			}
		}
	}).error(function(data) {
		console.log("ERROR: " + data);
	});
}

var identifier
function recaptchaSuccess() {
	$("#robot_form").submit();
}

function showRecaptchaModal(id) {
	$('#cyborgModal').modal("show");
	$('#robot_identifier').val(id);
}

function revokeToken() {
	var token = $('#revoke_access_token').val();
	if (token != null && token != "") {
		$.get("https://twitchtokengenerator.com/api/revoke/" + token, function(data){
			if(data.success) {
				$('#revoke_access_token').val("");
				alert("Token revoked successfully!");
			} else {
				alert("Token failed to be revoked! Details:\n\n" + data.message);
			}
		});
	}
}

function copyInput(btn, el) {
	var copyText = document.getElementById(el);
	copyText.select();
	document.execCommand("Copy");
	$(btn).html("Copied!");
	delay(function() {
		$(btn).html("Copy");
	}, 5000);
	addButtonMetrics($(btn).attr('id'));
}

// Source: https://stackoverflow.com/a/28173606
var delay = ( function() {
    var timer = 0;
    return function(callback, ms) {
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

// Source: https://stackoverflow.com/questions/1144783/how-to-replace-all-occurrences-of-a-string-in-javascript
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};