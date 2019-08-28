<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

interface IRotationManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 * @param int                                                     $degrees
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function rotate(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, int $degrees): void;
}
