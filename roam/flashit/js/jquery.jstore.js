(function($) {

	var supported = true;
	if (typeof JSON != 'undefined' && (typeof localStorage != 'undefined' || typeof globalStorage != 'undefined'))
		if (typeof localStorage != 'undefined')
			var ls = localStorage;
		else
			var ls = globalStorage[location.hostname];
	else
		supported = false;

	$.setItem = function(key, value) {
		if (!supported)
			return false;
		ls.setItem(key, JSON.stringify(value));
	};

	$.getItem = function(key) {
		if (!supported)
			return false;
		return JSON.parse(ls.getItem(key));
	};

	$.removeItem = function(key) {
		if (!supported)
			return false;
		ls.removeItem(key);
		return true;
	};

})(jQuery);
