<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Doctrine;
use SixtyEightPublishers;

abstract class AbstractPersistentSortableManipulator extends AbstractSortableManipulator
{
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
	 * Return sorted image!
	 *
	 * @param \Doctrine\ORM\EntityManagerInterface                         $em
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage      $sortedImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $previousImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $nextImage
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 */
	abstract public function doSortProcess(Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;

	/**
	 * {@inheritDoc}
	 */
	public function doSort(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): bool
	{
		$transaction = $this->transactionFactory->create([$this, 'doSortProcess'])
			->catch(SixtyEightPublishers\ImageBundle\Exception\IException::class, static function (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
				throw $e;
			})
			->error(static function (SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException $e) use ($sortedImage) {
				throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('sort', (string) $sortedImage->getSource(), 0, $e);
			})
			->immutable($sortedImage, $previousImage, $nextImage);

		$this->extendTransaction($transaction);

		$transaction->run();

		return TRUE;
	}
}
