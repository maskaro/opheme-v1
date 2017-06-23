//for (var prop in job_id) { alert(prop + " = " + job_id[prop]); }

var url = document.URL.split('?'),
	cnt = url.length;
if (cnt == 2 && url[cnt-1].indexOf('session_id') > -1) {
	var current_page = url[0].split('/'),
		capitaliseFirstLetter = function(string) { return string.charAt(0).toUpperCase() + string.slice(1); };
	history.replaceState('no_session_id', capitaliseFirstLetter(current_page[current_page.length - 1]), '/' + current_page[current_page.length - 1]);
}

$(function(){
	
	jQuery.validator.addMethod('selectcheck', function (value) {
		return (value != '----------');
	}, "Please select a valid option.");
	
	jQuery.validator.addMethod("containsPercentR", function(value, element, param) {
		return this.optional(element) || value.toLowerCase().indexOf('%r') !== -1;
	}, "You must enter %r");
	
	jQuery.validator.addMethod("containsPercentC", function(value, element, param) {
		return this.optional(element) || value.toLowerCase().indexOf('%c') !== -1;
	}, "You must enter %c");
	
});

function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}

//maps array filled with map handles and ids
var maps_json = {};

function hideAllBut(top_cls, cls, id) {
	if ($('.' + cls + id).is(':visible')) {
		$('.' + cls + id).fadeOut(300);
	} else {
		$('[class^="' + top_cls + '"]').each(function(index) {
			$(this).fadeOut(300);
		});
		$('.' + cls + id).fadeIn(300);
	}
}

function hide(cls, id) {
	$('.' + cls + id).fadeOut(300);
}