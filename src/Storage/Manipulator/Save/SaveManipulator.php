<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Save;

use Nette;
use Doctrine;
use SixtyEightPublishers;

class SaveManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\AbstractManipulator implements ISaveManipulator, SixtyEightPublishers\ImageBundle\Storage\Manipulator\IExternalAssociationStorageAware
{
	use SixtyEightPublishers\ImageBundle\Storage\Manipulator\TExtendableTransaction,
		SixtyEightPublishers\ImageBundle\Storage\Manipulator\TExternalAssociationStorageAware;

	/** @var \SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory  */
	private $imageEntityFactory;

	/** @var \SixtyEightPublishers\ImageBundle\ResourceMetadata\IResourceMetadataFactory  */
	private $resourceMetadataFactory;

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/** @var string|NULL  */
	private $imageStorageName;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory         $imageEntityFactory
	 * @param \SixtyEightPublishers\ImageBundle\ResourceMetadata\IResourceMetadataFactory $resourceMetadataFactory
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider                    $imageStorageProvider
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory   $transactionFactory
	 * @param string|NULL                                                                 $imageStorageName
	 */
	public function __construct(
		SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory $imageEntityFactory,
		SixtyEightPublishers\ImageBundle\ResourceMetadata\IResourceMetadataFactory $resourceMetadataFactory,
		SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider,
		SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory,
		?string $imageStorageName = NULL
	) {
		$this->imageEntityFactory = $imageEntityFactory;
		$this->resourceMetadataFactory = $resourceMetadataFactory;
		$this->imageStorageProvider = $imageStorageProvider;
		$this->transactionFactory = $transactionFactory;
		$this->imageStorageName = $imageStorageName;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Exception
	 */
	public function createResource(Nette\Http\FileUpload $fileUpload, SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options): SixtyEightPublishers\ImageStorage\Resource\IResource
	{
		$helper = new Options($options);
		$storage = $this->imageStorageProvider->get($this->imageStorageName);
		$path = sprintf('%s/%s', $helper->getNamespace(), $helper->getSourceName($fileUpload));

		$resource = $storage->createResourceFromLocalFile(
			new SixtyEightPublishers\ImageStorage\ImageInfo($path),
			$fileUpload->getTemporaryFile()
		);

		$resource->modifyImage([
			'o' => 'auto',
			'pf' => '1',
		]);

		return  $resource;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageStorage\Resource\IResource $resource): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$helper = new Options($options);
		$storage = $this->imageStorageProvider->get($this->imageStorageName);
		$resourceMetadata = array_merge($this->resourceMetadataFactory->create($resource), $helper->getCustomMetadata());

		$storage->save($resource);

		$transaction = $this->transactionFactory->create(function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageStorage\ImageInfo $imageInfo) use ($resourceMetadata) {
			$image = $this->imageEntityFactory->create(
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoFactory::create($imageInfo, $this->imageStorageName)
			);

			$image->setMetadata($resourceMetadata);
			$em->persist($image);

			return $image;
		});

		# External associations
		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL !== $associationStorage) {
			$transaction->finally(static function (SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) use ($associationStorage) {
				$associationStorage->getReferences()->add(new SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference((string) $image->getId()));
				$associationStorage->flush();
			});
		}

		$transaction = $transaction->immutable($resource->getInfo(), $options);

		$this->extendTransaction($transaction);
		$helper->extendTransaction($transaction);

		return $transaction->run();
	}
}
