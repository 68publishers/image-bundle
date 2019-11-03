<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use Doctrine;
use SixtyEightPublishers;

class DeleteManipulator implements IDeleteManipulator
{
	use Nette\SmartObject;
	use TExtendableTransaction;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	 */
	public function __construct(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory)
	{
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrinePersistence\Transaction\Transaction
	 */
	protected function createSoftDeleteTransaction(): SixtyEightPublishers\DoctrinePersistence\Transaction\Transaction
	{
		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage $image) {
			$image->delete();
			$em->persist($image);

			return $image->getSource();
		});

		return $transaction;
	}

	/**
	 * @return \SixtyEightPublishers\DoctrinePersistence\Transaction\Transaction
	 */
	protected function createStandardDeleteTransaction(): SixtyEightPublishers\DoctrinePersistence\Transaction\Transaction
	{
		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) {
			$em->remove($image);

			return $image->getSource();
		});

		return $transaction;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function delete(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image): void
	{
		$transaction = $image instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage
			? $this->createSoftDeleteTransaction()
			: $this->createStandardDeleteTransaction();

		$transaction->catch(SixtyEightPublishers\ImageBundle\Exception\IException::class, static function (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
			throw $e;
		});

		$transaction->error(static function (SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException $e) use ($image) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('delete', (string) $image->getSource(), 0, $e);
		});

		$transaction = $transaction->immutable($image);

		$this->extendTransaction($transaction);

		$transaction->run();
	}
}
