<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Rotation;

use SixtyEightPublishers;

interface IRotationManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage    $image
	 * @param int                                                        $degrees
	 *
	 * @return void
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, int $degrees): void;
}
