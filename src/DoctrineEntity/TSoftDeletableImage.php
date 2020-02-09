<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * (!) ADD THIS ANNOTATION INTO YOUR ENTITY:
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
trait TSoftDeletableImage
{
	/**
	 * @var \DateTime|NULL
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $deletedAt;

	/**
	 * @param \DateTime|NULL $deletedAt
	 *
	 * @return void
	 */
	public function setDeletedAt(?\DateTime $deletedAt): void
	{
		$this->deletedAt = $deletedAt;
	}

	/**
	 * @return \DateTime|NULL
	 */
	public function getDeletedAt(): ?\DateTime
	{
		return $this->deletedAt;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return NULL !== $this->deletedAt;
	}
}
