<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use SixtyEightPublishers;

interface ISaveManipulator
{
	/**
	 * @param \Nette\Http\FileUpload                                                               $fileUpload
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions $options
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function save(Nette\Http\FileUpload $fileUpload, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;
}
