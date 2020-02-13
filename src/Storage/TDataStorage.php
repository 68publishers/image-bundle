<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use SixtyEightPublishers;

trait TDataStorage
{
	/** @var \SixtyEightPublishers\ImageBundle\Storage\Options\Options|NULL */
	private $options;

	/** @var \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator[]  */
	private $manipulators = [];

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getOptions(): Options\IOptions
	{
		if (NULL === $this->options) {
			$this->options = new Options\Options();
		}

		return $this->options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator(Manipulator\IManipulator $manipulator): void
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
	public function getManipulator(string $className): Manipulator\IManipulator
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

	/**
	 * @param string $manipulatorClassName
	 * @param mixed  ...$args
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function manipulate(string $manipulatorClassName, ...$args)
	{
		return $this->getManipulator($manipulatorClassName)->manipulate($this->getOptions(), ...$args);
	}
}
