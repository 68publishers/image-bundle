<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class UploadErrorEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.upload_error';

	/** @var \SixtyEightPublishers\ImageBundle\Exception\IException  */
	private $exception;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Exception\IException $exception
	 */
	public function __construct(SixtyEightPublishers\ImageBundle\Exception\IException $exception)
	{
		$this->exception = $exception;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Exception\IException
	 */
	public function getException(): SixtyEightPublishers\ImageBundle\Exception\IException
	{
		return $this->exception;
	}
}
