<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Metadata;

use Nette;
use SixtyEightPublishers;

final class Metadata implements IMetadata
{
	use Nette\SmartObject;

	/** @var array  */
	private $metadata = [];

	/************** interface \SixtyEightPublishers\ImageBundle\Storage\Metadata\IMetadata **************/

	/**
	 * {@inheritDoc}
	 */
	public function get(string $key, ?string $validator = NULL)
	{
		if (!$this->has($key)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Data for key "%s" not found.',
				$key
			));
		}

		if (NULL !== $validator && !Nette\Utils\Validators::is($this->metadata[$key], $validator)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Metadata for key "%s" doesn\'t match validator %s.',
				$key,
				$validator
			));
		}

		return $this->metadata[$key];
	}

	/**
	 * {@inheritDoc}
	 */
	public function has(string $key, ?string $validator = NULL): bool
	{
		if (!array_key_exists($key, $this->metadata)) {
			return FALSE;
		}

		return NULL === $validator ? TRUE : Nette\Utils\Validators::is($this->metadata[$key], $validator);
	}

	/**
	 * {@inheritDoc}
	 */
	public function set(string $key, $value): void
	{
		$this->metadata[$key] = $value;
	}
}
