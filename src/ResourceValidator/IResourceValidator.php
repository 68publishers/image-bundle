<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceValidator;

use SixtyEightPublishers;

interface IResourceValidator
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\IException
	 */
	public function validate(SixtyEightPublishers\ImageStorage\Resource\IResource $resource): void;
}
