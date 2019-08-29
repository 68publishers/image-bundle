<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity\SoftDeletable;

use SixtyEightPublishers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_IMAGE_CREATED", columns={"created"})
 * })
 */
final class Image extends SixtyEightPublishers\ImageBundle\DoctrineEntity\AbstractImage implements SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage
{
	use SixtyEightPublishers\ImageBundle\DoctrineEntity\TSoftDeletableImage;
}
