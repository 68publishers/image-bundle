<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use Nette;
use SixtyEightPublishers;

/**
 * @method void onSuccess()
 * @method void onError(\Throwable $e)
 */
abstract class AbstractAction implements IAction
{
	use Nette\SmartObject;

	/** @var string|NULL */
	protected $label;

	/**
	 * @param string $label
	 */
	public function __construct(?string $label = NULL)
	{
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	abstract protected function getManipulatorClass(): string;

	/**
	 * @param object                                                  $manipulator
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	abstract protected function doRun($manipulator, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void;

	/*************** interface \AppBundle\Control\ImageManager\Action\AbstractAction ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getLabel(): string
	{
		return $this->label ?? $this->getName();
	}

	/**
	 * {@inheritdoc}
	 */
	public function canBeUsed(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage): bool
	{
		$manipulatorClass = $this->getManipulatorClass();

		return $dataStorage->hasManipulator($manipulatorClass);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$this->doRun($dataStorage->getManipulator($this->getManipulatorClass()), $image);
	}
}
