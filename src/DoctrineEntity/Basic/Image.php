<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic;

use SixtyEightPublishers;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="IDX_IMAGE_CREATED", columns={"created"})
 * })
 */
class Image extends SixtyEightPublishers\ImageBundle\DoctrineEntity\AbstractImage
{
}
