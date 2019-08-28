<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

interface IDeleteManipulator
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function delete(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void;
}
