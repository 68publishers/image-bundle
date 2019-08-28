<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\EntityFactory;

use SixtyEightPublishers;

final class DefaultImageEntityFactory implements IImageEntityFactory
{
	/************** interface \SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory **************/

	/**
	 * {@inheritdoc}
	 */
	public function create(SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $imageInfo): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		return new SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic\Image($imageInfo);
	}
}
