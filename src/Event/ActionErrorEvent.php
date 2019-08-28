<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class ActionErrorEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.action_error';

	/** @var string  */
	private $actionName;

	/** @var \SixtyEightPublishers\ImageBundle\Exception\IException  */
	private $exception;

	/**
	 * @param string                                                 $actionName
	 * @param \SixtyEightPublishers\ImageBundle\Exception\IException $exception
	 */
	public function __construct(string $actionName, SixtyEightPublishers\ImageBundle\Exception\IException $exception)
	{
		$this->actionName = $actionName;
		$this->exception = $exception;
	}

	/**
	 * @return string
	 */
	public function getActionName(): string
	{
		return $this->actionName;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Exception\IException
	 */
	public function getException(): SixtyEightPublishers\ImageBundle\Exception\IException
	{
		return $this->exception;
	}
}
