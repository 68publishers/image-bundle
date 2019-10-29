<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceMetadata;

use SixtyEightPublishers;

interface IResourceMetadataFactory
{
	/**
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResource $resource
	 *
	 * @return array
	 */
	public function create(SixtyEightPublishers\ImageStorage\Resource\IResource $resource): array;
}
