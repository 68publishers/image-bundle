<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\EntityFactory;

use Nette;
use SixtyEightPublishers;

final class DefaultImageEntityFactory implements IImageEntityFactory
{
	use Nette\SmartObject;

	/** @var string  */
	private $className;

	/**
	 * @param string $className
	 */
	public function __construct(string $className)
	{
		$this->className = $className;
	}

	/************** interface \SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory **************/

	/**
	 * {@inheritdoc}
	 */
	public function create(SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo $imageInfo): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$class = $this->className;

		return new $class($imageInfo);
	}
}
