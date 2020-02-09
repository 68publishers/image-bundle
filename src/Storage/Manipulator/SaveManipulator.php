<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use Nette;
use Doctrine;
use Intervention;
use SixtyEightPublishers;

class SaveManipulator implements ISaveManipulator
{
	use Nette\SmartObject;
	use TExtendableTransaction;

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
	public function createResource(Nette\Http\FileUpload $fileUpload, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageStorage\Resource\IResource
	{
		$storage = $this->imageStorageProvider->get($this->imageStorageName);
		$path = sprintf('%s/%s', $options->createNamespace($fileUpload), $options->createSourceName($fileUpload));

		try {
			$resource = $storage->createResourceFromLocalFile(
				new SixtyEightPublishers\ImageStorage\ImageInfo($path),
				$fileUpload->getTemporaryFile()
			);

			$resource->modifyImage([
				'o' => 'auto',
				'pf' => '1',
			]);

			return  $resource;
		} catch (SixtyEightPublishers\ImageStorage\Exception\ImageInfoException $e) {
		} catch (Intervention\Image\Exception\ImageException $e) {
		} catch (SixtyEightPublishers\ImageStorage\Exception\IException $e) {
		}

		throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('save - resource creating', $path, 0, $e ?? NULL);
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(SixtyEightPublishers\ImageStorage\Resource\IResource $resource, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$storage = $this->imageStorageProvider->get($this->imageStorageName);
		$metadata = array_merge($this->resourceMetadataFactory->create($resource), $options->getCustomMetadata());

		try {
			$storage->save($resource);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FilesystemException $e) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('save - resource saving', (string) $resource->getInfo(), 0, $e);
		}

		$transaction = $this->transactionFactory->create(function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageStorage\ImageInfo $imageInfo) use ($metadata) {
			$image = $this->imageEntityFactory->create(
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoFactory::create($imageInfo, $this->imageStorageName)
			);

			$image->setMetadata($metadata);
			$em->persist($image);

			return $image;
		});

		$transaction->catch(SixtyEightPublishers\ImageBundle\Exception\IException::class, static function (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
			throw $e;
		});

		$transaction->error(static function (SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException $e) use ($resource) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('save - doctrine persistence', (string) $resource->getInfo(), 0, $e);
		});

		$transaction = $transaction->immutable($resource->getInfo());

		$this->extendTransaction($transaction);
		$options->doExtendTransaction($transaction);

		return $transaction->run();
	}
}
