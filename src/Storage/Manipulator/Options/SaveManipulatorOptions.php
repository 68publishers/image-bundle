<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options;

use Nette;
use SixtyEightPublishers;

class SaveManipulatorOptions
{
	use Nette\SmartObject;

	/** @var string|callable  */
	private $namespace = '';

	/** @var NULL|string|callable */
	private $sourceName;

	/** @var NULL|callable */
	private $transactionExtensionCallback;

	/** @var array  */
	private $customMetadata = [];

	/**
	 * @param string $namespace
	 *
	 * @return void
	 */
	public function setNamespace(string $namespace): void
	{
		$this->namespace = $namespace;
	}

	/**
	 * @param string $sourceName
	 *
	 * @return void
	 */
	public function setSourceName(string $sourceName): void
	{
		$this->sourceName = $sourceName;
	}

	/**
	 * The only argument for callback is a Nette\Http\FileUpload object
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function setNamespaceCallback(callable $callback): void
	{
		$this->namespace = $callback;
	}

	/**
	 * The only argument for callback is a Nette\Http\FileUpload object
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function setSourceNameCallback(callable $callback): void
	{
		$this->sourceName = $callback;
	}

	/**
	 * The only argument for callback is a ITransaction object
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function setTransactionExtensionCallback(callable $callback): void
	{
		$this->transactionExtensionCallback = $callback;
	}

	/**
	 * @param array $metadata
	 *
	 * @return void
	 */
	public function setCustomMetadata(array $metadata): void
	{
		$this->customMetadata = $metadata;
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function addCustomMetadata(string $name, $value): void
	{
		$this->customMetadata[$name] = $value;
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return string
	 */
	public function createNamespace(Nette\Http\FileUpload $fileUpload): string
	{
		if (is_callable($this->namespace)) {
			$cb = $this->namespace;

			return (string) $cb($fileUpload);
		}

		return (string) $this->namespace;
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return string
	 */
	public function createSourceName(Nette\Http\FileUpload $fileUpload): string
	{
		if (is_callable($this->sourceName)) {
			$cb = $this->sourceName;

			return (string) $cb($fileUpload);
		}

		return $fileUpload->getSanitizedName();
	}

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction
	 *
	 * @return void
	 */
	public function doExtendTransaction(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction): void
	{
		if (is_callable($this->transactionExtensionCallback)) {
			$cb = $this->transactionExtensionCallback;

			$cb($transaction);
		}
	}

	/**
	 * @return array
	 */
	public function getCustomMetadata(): array
	{
		return $this->customMetadata;
	}
}
