<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use SixtyEightPublishers;

abstract class AbstractManipulator implements IManipulator
{
	use Nette\SmartObject;

	/**
	 * {@inheritDoc}
	 *
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidStateException
	 */
	public function manipulate(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, ...$args)
	{
		$cb = $this;

		if (!is_callable($cb)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Class %s is not callable, please implement method %s::__invoke().',
				static::class,
				static::class
			));
		}

		try {
			return $cb($options, ...$args);
		} catch (SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException $e) {
			throw $e;
		} catch (\Throwable $e) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error(static::class, 0, $e);
		}
	}
}
