<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

interface ISoftDeletableImage extends IImage
{
	/**
	 * @return void
	 */
	public function delete(): void;

	/**
	 * @return void
	 */
	public function restore(): void;

	/**
	 * @return bool
	 */
	public function isDeleted(): bool;
}
