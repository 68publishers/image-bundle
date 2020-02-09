<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class ArrayDataStorage implements IDataStorage
{
	use Nette\SmartObject,
		TManipulators;

	/** @var \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[]  */
	protected $images = [];

	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[] $images
	 */
	public function __construct(array $images = [])
	{
		foreach ($images as $image) {
			$this->addImage($image);
		}
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 *
	 * @return void
	 */
	public function addImage(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$this->images[] = $image;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getImages(): Doctrine\Common\Collections\Collection
	{
		return new Doctrine\Common\Collections\ArrayCollection($this->images);
	}
}
