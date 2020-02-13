<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use Nette;
use SixtyEightPublishers;

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
}
