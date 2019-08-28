<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use Doctrine;
use SixtyEightPublishers;

class RotationManipulator implements IRotationManipulator
{
	use Nette\SmartObject;
	use TExtendableTransaction;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/**
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider                  $imageStorageProvider
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	 */
	public function __construct(
		SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider,
		SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	) {
		$this->imageStorageProvider = $imageStorageProvider;
		$this->transactionFactory = $transactionFactory;
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image
	 * @param int                                                     $degrees
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	private function rotateSource(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, int $degrees): void
	{
		$source = $image->getSource();
		$imageStorage = $this->imageStorageProvider->get($source->getStorageName());

		try {
			$resource = $imageStorage->createResource($source);

			$resource->modifyImage([
				'o' => (string) $degrees,
				'pf' => '1',
			]);

			$imageStorage->updateOriginal($resource);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FilesystemException $e) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('rotation', (string) $source, 0, $e);
		}
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function rotate(SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, int $degrees): void
	{
		$this->rotateSource($image, $degrees);

		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) {
			$image->update();
			$em->persist($image);
		})->immutable($image);

		$this->extendTransaction($transaction);
		$transaction->run();
	}
}
