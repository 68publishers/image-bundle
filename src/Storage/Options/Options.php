<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Options;

use Nette;
use SixtyEightPublishers;

final class Options implements IOptions
{
	use Nette\SmartObject;

	/** @var array  */
	private $options = [];

	/************** interface \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions **************/

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, ?string $validator = NULL)
	{
		if (!$this->has($key)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Value for key "%s" not found.',
				$key
			));
		}

		if (NULL !== $validator && !Nette\Utils\Validators::is($this->options[$key], $validator)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Value for key "%s" doesn\'t match validator %s.',
				$key,
				$validator
			));
		}

		return $this->options[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key, ?string $validator = NULL): bool
	{
		if (!array_key_exists($key, $this->options)) {
			return FALSE;
		}

		return NULL === $validator ? TRUE : Nette\Utils\Validators::is($this->options[$key], $validator);
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, $value): void
	{
		$this->options[$key] = $value;
	}
}
