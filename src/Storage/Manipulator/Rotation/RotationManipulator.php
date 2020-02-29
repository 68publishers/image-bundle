<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Rotation;

use Doctrine;
use SixtyEightPublishers;

class RotationManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\AbstractManipulator implements IRotationManipulator
{
	use SixtyEightPublishers\ImageBundle\Storage\Manipulator\TExtendableTransaction;

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

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Rotation\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 *
	 * @throws \SixtyEightPublishers\ImageStorage\Exception\FilesystemException
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, int $degrees): void
	{
		$source = $image->getSource();
		$imageStorage = $this->imageStorageProvider->get($source->getStorageName());
		$resource = $imageStorage->createResource($source);

		$resource->modifyImage([
			'o' => (string) $degrees,
			'pf' => '1',
		]);

		$imageStorage->update($resource);

		$transaction = $this->transactionFactory->create(static function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) {
			$image->update();
			$em->persist($image);
		})->immutable($image, $options);

		$this->extendTransaction($transaction);
		$transaction->run();
	}
}
