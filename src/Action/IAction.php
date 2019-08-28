<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use SixtyEightPublishers;

interface IAction
{
	/**
	 * @return string
	 */
	public function getName(): string;

	/**
	 * @return string
	 */
	public function getLabel(): string;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\IDataStorage $storage
	 *
	 * @return bool
	 */
	public function canBeUsed(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $storage): bool;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\IDataStorage  $dataStorage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function run(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void;
}
