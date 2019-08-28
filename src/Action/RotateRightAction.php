<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Action;

use SixtyEightPublishers;

final class RotateRightAction extends AbstractAction
{
	/**
	 * {@inheritdoc}
	 */
	public function getName(): string
	{
		return 'rotate_right';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getManipulatorClass(): string
	{
		return SixtyEightPublishers\ImageBundle\Storage\Manipulator\IRotationManipulator::class;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IRotationManipulator $manipulator
	 */
	protected function doRun($manipulator, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$manipulator->rotate($image, -90);
	}
}
