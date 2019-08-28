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
		return SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator $manipulator
	 */
	protected function doRun($manipulator, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$manipulator->delete($image);
	}
}
