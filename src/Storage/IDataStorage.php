<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Doctrine;

interface IDataStorage
{
	/**
	 * @return \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions
	 */
	public function getOptions(): Options\IOptions;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator $manipulator
	 *
	 * @return void
	 */
	public function addManipulator(Manipulator\IManipulator $manipulator): void;

	/**
	 * @param string $className
	 *
	 * @return bool
	 */
	public function hasManipulator(string $className): bool;

	/**
	 * @param string $className
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function getManipulator(string $className): Manipulator\IManipulator;

	/**
	 * @param string $manipulatorClassName
	 * @param mixed  ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function manipulate(string $manipulatorClassName, ...$args);

	/**
	 * @return \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[]
	 */
	public function getImages(): Doctrine\Common\Collections\Collection;
}
