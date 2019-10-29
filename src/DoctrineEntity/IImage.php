<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DoctrineEntity;

use SixtyEightPublishers;

interface IImage
{
	/**
	 * @return mixed
	 */
	public function getId();

	/**
	 * @return \SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo
	 */
	public function getSource(): SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfo;

	/**
	 * @return \DateTime
	 */
	public function getCreated(): \DateTime;

	/**
	 * @param string|NULL $key
	 * @param mixed|NULL  $default
	 *
	 * @return mixed|array|NULL
	 */
	public function getMetadata(string $key = NULL, $default = NULL);

	/**
	 * @param array $metadata
	 *
	 * @return void
	 */
	public function setMetadata(array $metadata): void;

	/**
	 * Updates the version of a source and a `updated` field
	 *
	 * @return void
	 */
	public function update(): void;
}
