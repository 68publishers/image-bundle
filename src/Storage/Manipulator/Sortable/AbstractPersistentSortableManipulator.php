<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Sortable;

use Doctrine;
use SixtyEightPublishers;

abstract class AbstractPersistentSortableManipulator extends AbstractSortableManipulator
{
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
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions   $options
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage      $sortedImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $previousImage
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage|NULL $nextImage
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 */
	abstract public function doSortProcess(Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage;

	/**
	 * {@inheritDoc}
	 */
	public function doSort(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $sortedImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $previousImage, ?SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $nextImage): bool
	{
		$this->transactionFactory->create([$this, 'doSortProcess'])
			->immutable($options, $sortedImage, $previousImage, $nextImage)
			->run();

		return TRUE;
	}
}
