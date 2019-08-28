<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use SixtyEightPublishers;

trait TManipulators
{
	/** @var array  */
	private $manipulators = [];

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator($manipulator): void
	{
		$this->manipulators[] = $manipulator;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasManipulator(string $className): bool
	{
		try {
			$this->getManipulator($className);

			return TRUE;
		} catch (SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException $e) {
			return FALSE;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getManipulator(string $className)
	{
		foreach ($this->manipulators as $manipulator) {
			if ($manipulator instanceof $className) {
				return $manipulator;
			}
		}

		throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
			'Manipulator %s is not defined in DataStorage.',
			$className
		));
	}
}
