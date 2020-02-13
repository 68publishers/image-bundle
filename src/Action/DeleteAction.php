<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use SixtyEightPublishers;

final class DeleteAction extends AbstractAction
{
	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'delete';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getManipulatorClass(): string
	{
		return SixtyEightPublishers\ImageBundle\Storage\Manipulator\Delete\IDeleteManipulator::class;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$dataStorage->manipulate($this->getManipulatorClass(), $image);
	}
}
