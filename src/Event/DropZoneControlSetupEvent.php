<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Event;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class DropZoneControlSetupEvent extends Symfony\Contracts\EventDispatcher\Event
{
	use Nette\SmartObject;

	public const NAME = 'image_bundle.drop_zone_control_setup';

	/** @var \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl  */
	private $dropZoneControl;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl $dropZoneControl
	 */
	public function __construct(SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl $dropZoneControl)
	{
		$this->dropZoneControl = $dropZoneControl;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function getDropZoneControl(): SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	{
		return $this->dropZoneControl;
	}
}
