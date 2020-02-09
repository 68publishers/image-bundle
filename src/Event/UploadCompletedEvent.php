<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;

final class UploadCompletedEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.upload_completed';

	/** @var int  */
	private $filesCount;

	/**
	 * @param int $filesCount
	 */
	public function __construct(int $filesCount)
	{
		$this->filesCount = $filesCount;
	}

	/**
	 * @return int
	 */
	public function getFilesCount(): int
	{
		return $this->filesCount;
	}
}
