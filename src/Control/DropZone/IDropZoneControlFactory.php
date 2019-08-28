<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone;

use Symfony;

interface IDropZoneControlFactory
{
	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function create(Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher): DropZoneControl;
}
