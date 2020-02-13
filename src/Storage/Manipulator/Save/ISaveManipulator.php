<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Save;

use Nette;
use SixtyEightPublishers;

interface ISaveManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\IManipulator
{
	/**
	 * @param \Nette\Http\FileUpload                                     $fileUpload
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Resource\IResource
	 */
	public function createResource(Nette\Http\FileUpload $fileUpload, SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options): SixtyEightPublishers\ImageStorage\Resource\IResource;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource      $resource
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageStorage\Resource\IResource $resource): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;
}
