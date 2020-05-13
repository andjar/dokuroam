<?php

class Extended_Markdown_Parser extends Markdown_Parser {

	// Modifying the base Markdown so we can had an anchor tag around each image,
	// to work nicely with Fancybox.
	function _doImages_inline_callback($matches) {
		$whole_match	= $matches[1];
		$alt_text		= $matches[2];
		$url			= $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		$alt_text = $this->encodeAttribute($alt_text);
		$url = $this->encodeAttribute($url);
		$result = "<a href=\"$url\" class=\"fancybox\"><img src=\"$url\" alt=\"$alt_text\"";
		if (isset($title)) {
			$title = $this->encodeAttribute($title);
			$result .=  " title=\"$title\""; # $title already quoted
		}
		$result .= $this->empty_element_suffix;
		$result .= "</a>";

		return $this->hashPart($result);
	}

}

?>