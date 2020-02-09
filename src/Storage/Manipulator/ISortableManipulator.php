<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

interface ISortableManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage      $sortedImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|null $previousImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|null $nextImage
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function sort(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage = NULL, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage = NULL): void;
}
