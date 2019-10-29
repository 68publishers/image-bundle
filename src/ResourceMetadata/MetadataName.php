<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceMetadata;

use Nette;

/**
 * Enum with names of basic metadata.
 */
final class MetadataName
{
	use Nette\StaticClass;

	public const    WIDTH = 'width',
					HEIGHT = 'height',
					MIME = 'mime';
}
