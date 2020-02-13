<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\ImageManager;

use Nette;

final class ConfiguredImageManagerArgs
{
	use Nette\SmartObject;

	/** @var string  */
	private $name;

	/** @var array  */
	private $dataStorageArgs;

	/** @var array  */
	private $options = [];

	/**
	 * @param string $name
	 * @param mixed  ...$dataStorageArgs
	 */
	public function __construct(string $name, ...$dataStorageArgs)
	{
		$this->name = $name;
		$this->dataStorageArgs = $dataStorageArgs;
	}

	/**
	 * @param array $options
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerArgs
	 */
	public function setOptions(array $options): self
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * @param string $key
	 * @param $value
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerArgs
	 */
	public function addOption(string $key, $value): self
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function getDataStorageArgs(): array
	{
		return $this->dataStorageArgs;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}
}
