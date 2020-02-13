<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Sortable;

use SixtyEightPublishers;

interface ISortableManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions   $options
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage      $sortedImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $previousImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $nextImage
	 *
	 * @return void
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage = NULL, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage = NULL): void;
}
