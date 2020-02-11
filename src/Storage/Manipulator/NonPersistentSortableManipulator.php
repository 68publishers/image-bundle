<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

final class NonPersistentSortableManipulator extends AbstractSortableManipulator
{
	/**
	 * {@inheritDoc}
	 */
	public function doSort(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): bool
	{
		return TRUE;
	}
}
