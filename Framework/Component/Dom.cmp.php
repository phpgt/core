<?php
class Dom implements ArrayAccess {
	private $_domDoc = null;

	/**
	* Dom is a wrapper to PHP's native DOMDocument which adds many features to
	* boost development efficiency. The most notable feature is allowing
	* manipulation via CSS selectors.
	* @param string $html Whole document to parse in HTML format. This should
	* only ever be passed in via the Response object by flushing the output
	* buffer.
	*/
	public function __construct($html) {
		$this->_domDoc = new DomDocument("1.0", "utf-8");
		libxml_use_internal_errors(true);
		if(!$this->_domDoc->loadHTML($html) ) {
			// TODO: Throw and log a proper error.
			die("Error loading HTML into Dom");
		}
	}

	/**
	* Checks to see if a given CSS selector exists matches any DOM Elements.
	* @param string $selector CSS selector to check.
	* @return bool Whether the CSS selector matches any DOM Elements.
	*/
	public function offsetExists($selector) {
		// TODO: Implement offsetExists.
		return null;
	}

	/**
	* Returns an array of Dom elements (type DomEl) that match the 
	* provided CSS selector.
	* @param string $selector CSS selector to match.
	* @param DOMNode|DomEl $contextNode Optional. The sub-node to query.
	* @return array An array of matching DomEl objects.
	*/
	public function offsetGet($selector, $contextNode = null) {
		if(!is_null($contextNode)) {
			if($contextNode instanceof DomEl) {
				$contextNode = $contextNode->node;
			}
		}

		$xQuery = new CssXpath_Utility($selector);
		$xpath = new DOMXPath($this->_domDoc);

		// Remove double slash if a context is given.
		if(!is_null($contextNode)) {
			if(strpos($xQuery, "//") === 0) {
				$xQuery = substr($xQuery, 2);
			}
		}

		$domNodeList = $xpath->query($xQuery, $contextNode);

		return new DomElCollection($this, $domNodeList);
	}

	/**
	* Replaces zero or more DOM Elements that match the given CSS selector with
	* another DOM Element.
	* @param string $selector CSS selector describing element(s) to replace.
	* @param DomEl|DomElCollection $value The element to replace with.
	* @return int The number of elements that were replaced.
	*/
	public function offsetSet($selector, $value) {
		// TODO: Implement offsetSet.
		return 0;
	}

	/**
	* Removes a given CSS selector from the DOM. If more than one element
	* matches the given selector, all matches will be removed.
	* @param string $selector CSS selector describing element(s) to remove.
	* @return int The number of elements that were removed.
	*/
	public function offsetUnset($selector) {
		// TODO: Implement offsetUnset.
		return 0;
	}

	public function getDomDoc() {
		return $this->_domDoc;
	}

	/**
	* Wrapper to create a new DomEl object while keeping reference to the DOM.
	* @param string|DomEl|DomElement The new element to create.
	* @param array $attrArray Optional. An associative array of attributes to
	* assign to the newly created DomEl.
	* @param string $value Optional. The initial text value of the element.
	* @return DomEl The newly created DomEl object.
	*/
	public function create(
	$el,
	$attrArray  = null,
	$value      = null) {

		return new DomEl($this, $el, $attrArray, $value);
	}

	/**
	* Outputs the HTML contained within the DOM to the output buffer and
	* instantly flushes the buffer to the browser.
	*/
	public function flush() {
		ob_clean();
		$this->_domDoc->formatOutput = true;
		echo $this->_domDoc->saveHTML();
		ob_flush();
	}

	/**
	* Searches the DOM for elements with the phpgt attribute. All elements get
	* removed from the DOM and stored in an associative array.
	* @return array An array of DomEl objects that have their phpgt attribute
	* values as the array keys.
	*/
	public function scrape($attribute = "data-phpgt-scrape") {
		$xpath = new DOMXPath($this->_domDoc);
		$domNodeList = $xpath->query("//*[@{$attribute}]");
		$domNodeListLength = $domNodeList->length;

		$domNodeArray = array();

		for($i = 0; $i < $domNodeListLength; $i++) {
			$item = $domNodeList->item($i);
			$domNodeArray[$item->getAttribute($attribute)] = 
				new DomEl($this, $item);
		}

		$domNodeCollection = new DomElCollection($this, $domNodeArray);

		// Remove the collection from the DOM (wherever it may be) and place it
		// into a DIV with id of PHPGt_Scraped_Elements for picking up in Gt.js.		
		$body = $this->_domDoc->getElementsByTagName("body");
		if($body->length > 0) {
			$body = new DomEl($this, $body->item(0));

			$scrapeDiv = new DomEl(
				$this,
				"div",
				array(
					"id"	=> "PHPGt_Scraped_Elements",
					"style"	=> "display: none;"
				)
			);
			$scrapeDiv->append($domNodeCollection->cloneNodes());
			$body->append($scrapeDiv);
		}
		
		$domNodeCollection->map(function(&$element, $key, $c_attribute) {
			$element->removeAttribute($c_attribute);
		}, $attribute);

		$domNodeCollection->remove();
		return $domNodeCollection;
	}
}
?>