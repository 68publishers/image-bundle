<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

trait TSoftDeletableImage
{
	/**
	 * @ORM\Column(type="boolean")
	 *
	 * @var bool
	 */
	protected $deleted = FALSE;

	/**
	 * @return void
	 */
	public function delete(): void
	{
		$this->deleted = TRUE;
	}

	/**
	 * @return void
	 */
	public function restore(): void
	{
		$this->deleted = FALSE;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->deleted;
	}
}
