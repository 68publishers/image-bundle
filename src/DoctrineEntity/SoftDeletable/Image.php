<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity\SoftDeletable;

use SixtyEightPublishers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Image extends SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic\Image implements SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage
{
	use SixtyEightPublishers\ImageBundle\DoctrineEntity\TSoftDeletableImage;
}
