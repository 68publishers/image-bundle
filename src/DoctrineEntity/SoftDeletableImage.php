<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

use SixtyEightPublishers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SoftDeletableImage extends Image implements ISoftDeletableImage
{
	use TSoftDeletableImage;
}
