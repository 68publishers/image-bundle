<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers;

interface IFlaggableManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator
{
	/**
	 * @param string $flag
	 *
	 * @return bool
	 */
	public function isFlagSupported(string $flag): bool;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage    $image
	 * @param string                                                     $flag
	 * @param bool                                                       $unique
	 *
	 * @return void
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, string $flag, bool $unique = FALSE): void;
}
