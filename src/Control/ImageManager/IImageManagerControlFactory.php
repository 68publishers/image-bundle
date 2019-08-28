<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\ImageManager;

use SixtyEightPublishers;

interface IImageManagerControlFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function create(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage): ImageManagerControl;
}
