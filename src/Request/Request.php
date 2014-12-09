<?php
/**
 *
 * PHP.Gt (http://php.gt)
 * @copyright Copyright Ⓒ 2014 Bright Flair Ltd. (http://brightflair.com)
 * @license Apache Version 2.0, January 2004. http://www.apache.org/licenses
 */
namespace Gt\Request;

use \Gt\Core\ConfigObj;

class Request {

const TYPE_PAGE			= "TYPE_PAGE";
const TYPE_API			= "TYPE_API";

const METHOD_GET		= "METHOD_GET";
const METHOD_POST		= "METHOD_POST";
const METHOD_PUT		= "METHOD_PUT";
const METHOD_DELETE		= "METHOD_DELETE";
const METHOD_HEAD		= "METHOD_HEAD";
const METHOD_OPTIONS	= "METHOD_OPTIONS";

public $uri;
public $ext;
public $method;
public $headers;
public $indexFilename;
public $forceExtension;

public $config;

/**
 * @param string $uri The requested absolute uri
 * @param ConfigObj $config Request configuration object
 */
public function __construct($uri, ConfigObj $config) {
	$this->uri = $uri;
	$this->ext = pathinfo($uri, PATHINFO_EXTENSION);
	$this->config = $config;

	$this->method = isset($_SERVER["REQUEST_METHOD"])
		? $_SERVER["REQUEST_METHOD"]
		: null;
	$this->headers = new HeaderList($_SERVER);
	$this->indexFilename = $config->index_filename;
	$this->forceExtension = $config->force_extension;
}

/**
 * Returns the type of request made, whether it is to a page or an API.
 * @return mixed A Request type constant.
 */
public function getType() {
	$apiPrefix = "/" . $this->config->api_prefix;

	if(strpos($this->uri, $apiPrefix) === 0) {
		return self::TYPE_API;
	}

	return self::TYPE_PAGE;
}

}#