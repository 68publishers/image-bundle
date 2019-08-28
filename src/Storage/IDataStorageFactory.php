<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

interface IDataStorageFactory
{
	/**
	 * @param string $storageClassName
	 * @param mixed  ...$args
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Storage\IDataStorage
	 */
	public function create(string $storageClassName, ...$args): IDataStorage;
}
