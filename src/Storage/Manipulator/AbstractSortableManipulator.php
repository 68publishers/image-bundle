<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use SixtyEightPublishers;

abstract class AbstractSortableManipulator implements ISortableManipulator, IExternalAssociationStorageAware
{
	use Nette\SmartObject,
		TAssociationStorageAware;

	/**
	 * Return TRUE if everything is OK otherwise return FALSE or better throw an exception.
	 *
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage      $sortedImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $previousImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $nextImage
	 *
	 * @return bool
	 */
	abstract public function doSort(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): bool;

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISortableManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function sort(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage = NULL, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage = NULL): void
	{
		if (FALSE === $this->doSort($sortedImage, $previousImage, $nextImage)) {
			return;
		}

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL === $associationStorage) {
			return;
		}

		$references = $associationStorage->getReferences();
		$sortedReference = $references->find((string) $sortedImage->getId());

		if (NULL === $sortedReference) {
			return;
		}

		$previousReference = NULL !== $previousImage ? $references->find((string) $previousImage->getId()) : NULL;

		if (NULL !== $previousReference) {
			$references->moveAfter($sortedReference, $previousReference);
			$associationStorage->flush();

			return;
		}

		$nextReference = NULL !== $nextImage ? $references->find((string) $nextImage->getId()) : NULL;

		if (NULL !== $nextReference) {
			$references->moveBefore($sortedReference, $nextReference);
			$associationStorage->flush();
		}
	}
}
