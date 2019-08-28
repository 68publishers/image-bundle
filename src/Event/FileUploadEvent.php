<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;

final class FileUploadEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.file_upload';

	/** @var \Nette\Http\FileUpload  */
	private $fileUpload;

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 */
	public function __construct(Nette\Http\FileUpload $fileUpload)
	{
		$this->fileUpload = $fileUpload;
	}

	/**
	 * @return \Nette\Http\FileUpload
	 */
	public function getFileUpload(): Nette\Http\FileUpload
	{
		return $this->fileUpload;
	}
}
