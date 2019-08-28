<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class ImageUploadedEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.image_uploaded';

	/** @var \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage  */
	private $image;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 */
	public function __construct(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image)
	{
		$this->image = $image;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 */
	public function getImage(): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		return $this->image;
	}
}
