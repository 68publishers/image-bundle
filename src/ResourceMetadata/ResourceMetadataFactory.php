<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceMetadata;

use SixtyEightPublishers;

class ResourceMetadataFactory implements IResourceMetadataFactory
{
	/*********** interface \SixtyEightPublishers\ImageBundle\ResourceMetadata\IResourceMetadataFactory ***********/

	/**
	 * {@inheritDoc}
	 */
	public function create(SixtyEightPublishers\ImageStorage\Resource\IResource $resource): array
	{
		$image = $resource->getImage();

		return [
			MetadataName::WIDTH => $image->width(),
			MetadataName::HEIGHT => $image->height(),
			MetadataName::MIME => $image->mime(),
		];
	}
}
