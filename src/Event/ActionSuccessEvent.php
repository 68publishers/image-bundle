<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class ActionSuccessEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.action_success';

	/** @var string  */
	private $actionName;

	/** @var \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage  */
	private $image;

	/**
	 * @param string                                                  $actionName
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 */
	public function __construct(string $actionName, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image)
	{
		$this->actionName = $actionName;
		$this->image = $image;
	}

	/**
	 * @return string
	 */
	public function getActionName(): string
	{
		return $this->actionName;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 */
	public function getImage(): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		return $this->image;
	}
}
