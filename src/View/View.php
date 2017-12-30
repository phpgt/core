<?php
namespace Gt\WebEngine\View;

use Psr\Http\Message\StreamInterface;

abstract class View {
	protected $outputStream;
	protected $viewModel;

	public function __construct(StreamInterface $outputStream, $viewModel) {
		$this->outputStream = $outputStream;
		$this->viewModel = $viewModel;
	}

	abstract public function getViewModel();

	public function stream():void {
		$this->outputStream->write((string)$this->viewModel);
	}
}