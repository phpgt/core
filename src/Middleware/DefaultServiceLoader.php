<?php /** @noinspection PhpUnused */
namespace Gt\WebEngine\Middleware;

use Gt\Config\Config;
use Gt\Database\Connection\DefaultSettings;
use Gt\Database\Connection\Settings;
use Gt\Database\Database;
use Gt\Dom\Document;
use Gt\DomTemplate\BindableCache;
use Gt\DomTemplate\Binder;
use Gt\DomTemplate\DocumentBinder;
use Gt\DomTemplate\ElementBinder;
use Gt\DomTemplate\HTMLAttributeBinder;
use Gt\DomTemplate\HTMLAttributeCollection;
use Gt\DomTemplate\ListBinder;
use Gt\DomTemplate\ListElementCollection;
use Gt\DomTemplate\PlaceholderBinder;
use Gt\DomTemplate\TableBinder;
use Gt\Http\Header\ResponseHeaders;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Http\Uri;
use Gt\ServiceContainer\Container;

class DefaultServiceLoader {
	public function __construct(
		protected Config $config,
		protected Container $container
	) {}

	public function loadResponseHeaders():ResponseHeaders {
		$response = $this->container->get(Response::class);
		return $response->headers;
	}

	public function loadDatabase():Database {
		$dbSettings = new Settings(
			$this->config->get("database.query_directory"),
			$this->config->get("database.driver"),
			$this->config->get("database.schema"),
			$this->config->get("database.host"),
			$this->config->get("database.port"),
			$this->config->get("database.username"),
			$this->config->get("database.password"),
			$this->config->get("database.connection_name") ?: DefaultSettings::DEFAULT_NAME,
			$this->config->get("database.collation") ?: DefaultSettings::DEFAULT_COLLATION,
			$this->config->get("database.charset") ?: DefaultSettings::DEFAULT_CHARSET,
		);
		return new Database($dbSettings);
	}

	public function loadBindableCache():BindableCache {
		return new BindableCache();
	}

	public function loadHTMLAttributeBinder():HTMLAttributeBinder {
		return new HTMLAttributeBinder();
	}

	public function loadHTMLAttributeCollection():HTMLAttributeCollection {
		return new HTMLAttributeCollection();
	}

	public function loadPlaceholderBinder():PlaceholderBinder {
		return new PlaceholderBinder();
	}

	public function loadElementBinder():ElementBinder {
		$elementBinder = new ElementBinder();
		$elementBinder->setDependencies(
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
		return $elementBinder;
	}

	public function loadTableBinder():TableBinder {
		$tableBinder = new TableBinder();
		$tableBinder->setDependencies(
			$this->container->get(ListBinder::class),
			$this->container->get(ListElementCollection::class),
			$this->container->get(ElementBinder::class),
			$this->container->get(HTMLAttributeBinder::class),
			$this->container->get(HTMLAttributeCollection::class),
			$this->container->get(PlaceholderBinder::class),
		);
		return $tableBinder;
	}

	public function loadListElementCollection():ListElementCollection {
		return new ListElementCollection(
			$this->container->get(Document::class),
		);
	}

	public function loadListBinder():ListBinder {
		$listBinder = new ListBinder();
		$listBinder->setDependencies(
			$this->container->get(ElementBinder::class),
			$this->container->get(ListElementCollection::class),
			$this->container->get(BindableCache::class),
			$this->container->get(TableBinder::class),
		);
		return $listBinder;
	}

	public function loadBinder():Binder {
		$document = $this->container->get(Document::class);
		$binder = new DocumentBinder($document);
		$binder->setDependencies(
			$this->container->get(ElementBinder::class),
			$this->container->get(PlaceholderBinder::class),
			$this->container->get(TableBinder::class),
			$this->container->get(ListBinder::class),
			$this->container->get(ListElementCollection::class),
			$this->container->get(BindableCache::class),
		);
		return $binder;
	}

	public function loadDocumentBinder():DocumentBinder {
		/** @var DocumentBinder $documentBinder */
		$documentBinder = $this->loadBinder();
		return $documentBinder;
	}

	public function loadRequestUri():Uri {
		return $this->container->get(Request::class)->getUri();
	}
}
