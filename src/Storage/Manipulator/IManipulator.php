<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

interface IManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 * @param mixed                                                      ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function manipulate(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, ...$args);
}
