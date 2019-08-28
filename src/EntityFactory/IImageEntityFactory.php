<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\EntityFactory;

use SixtyEightPublishers;

interface IImageEntityFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $imageInfo
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \Exception
	 */
	public function create(SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $imageInfo): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;
}
