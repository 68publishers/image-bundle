<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use Nette;
use SixtyEightPublishers;

final class SetFlagAction implements IAction
{
	use Nette\SmartObject;

	/** @var string  */
	private $name;

	/** @var string  */
	private $flag;

	/** @var bool  */
	private $unique;

	/** @var string */
	private $label;

	/**
	 * @param string      $name
	 * @param string      $flag
	 * @param bool        $unique
	 * @param string|NULL $label
	 */
	public function __construct(string $name, string $flag, bool $unique = FALSE, ?string $label = NULL)
	{
		$this->name = $name;
		$this->flag = $flag;
		$this->unique = $unique;
		$this->label = $label ?? $name;
	}

	/*************** interface \AppBundle\Control\ImageManager\Action\AbstractAction ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel(): string
	{
		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function canBeUsed(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage): bool
	{
		if (!$dataStorage->hasManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable\IFlaggableManipulator::class)) {
			return FALSE;
		}

		/** @var \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable\IFlaggableManipulator $manipulator */
		$manipulator = $dataStorage->getManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable\IFlaggableManipulator::class);

		return $manipulator->isFlagSupported($this->flag);
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$dataStorage->manipulate(SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable\IFlaggableManipulator::class, $image, $this->flag, $this->unique);
	}
}
