<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use SixtyEightPublishers;

final class DataStorageFactory implements IDataStorageFactory
{
	/************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorageFactory **************/

	/**
	 * {@inheritdoc}
	 */
	public function create(string $storageClassName, ...$args): IDataStorage
	{
		if (!is_subclass_of($storageClassName, IDataStorage::class, TRUE)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Class %s is not implementor of interface %s',
				$storageClassName,
				IDataStorage::class
			));
		}

		return new $storageClassName(...$args);
	}
}
