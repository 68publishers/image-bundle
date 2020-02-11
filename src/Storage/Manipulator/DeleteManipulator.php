<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use Doctrine;
use SixtyEightPublishers;

class DeleteManipulator implements IDeleteManipulator, IExternalAssociationStorageAware
{
	use Nette\SmartObject,
		TExtendableTransaction,
		TAssociationStorageAware;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	 */
	public function __construct(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory)
	{
		$this->transactionFactory = $transactionFactory;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function delete(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) {
			$em->remove($image);

			return $image->getSource();
		});

		$transaction->catch(SixtyEightPublishers\ImageBundle\Exception\IException::class, static function (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
			throw $e;
		});

		$transaction->error(static function (SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException $e) use ($image) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('delete', (string) $image->getSource(), 0, $e);
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

		$transaction = $transaction->immutable($image);

		$this->extendTransaction($transaction);

		$transaction->run();
	}
}
