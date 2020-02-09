<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

interface ISoftDeletableImage extends IImage
{
	/**
	 * @param \DateTime|NULL $deletedAt
	 */
	public function setDeletedAt(?\DateTime $deletedAt): void;

	/**
	 * @return \DateTime|NULL
	 */
	public function getDeletedAt(): ?\DateTime;

	/**
	 * @return bool
	 */
	public function isDeleted(): bool;
}
