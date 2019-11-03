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
	 * @return \SixtyEightPublishers\ImageStorage\Resource\IResource
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function createResource(Nette\Http\FileUpload $fileUpload, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageStorage\Resource\IResource;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource                                $resource
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions $options
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;
}
