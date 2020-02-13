<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Delete;

use Doctrine;
use SixtyEightPublishers;

class DeleteManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\AbstractManipulator implements IDeleteManipulator, SixtyEightPublishers\ImageBundle\Storage\Manipulator\IExternalAssociationStorageAware
{
	use SixtyEightPublishers\ImageBundle\Storage\Manipulator\TExtendableTransaction,
		SixtyEightPublishers\ImageBundle\Storage\Manipulator\TAssociationStorageAware;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	 */
	public function __construct(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory)
	{
		$this->transactionFactory = $transactionFactory;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDelete\DeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) {
			$em->remove($image);

			return $image->getSource();
		});

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL !== $associationStorage) {
			$transaction->finally(static function () use ($associationStorage, $image) {
				$references = $associationStorage->getReferences();
				$reference = $references->find((string) $image->getId());

				if (NULL === $reference) {
					return;
				}

				$references->remove($reference);
				$associationStorage->flush();
			});
		}

		$transaction = $transaction->immutable($image, $options);

		$this->extendTransaction($transaction);

		$transaction->run();
	}
}
