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

	/** @var \SixtyEightPublishers\ImageStorage\IImageStorageProvider  */
	private $imageStorageProvider;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/** @var string|NULL  */
	private $imageStorageName;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory       $imageEntityFactory
	 * @param \SixtyEightPublishers\ImageStorage\IImageStorageProvider                  $imageStorageProvider
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory
	 * @param string|NULL                                                               $imageStorageName
	 */
	public function __construct(
		SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory $imageEntityFactory,
		SixtyEightPublishers\ImageStorage\IImageStorageProvider $imageStorageProvider,
		SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory,
		?string $imageStorageName = NULL
	) {
		$this->imageEntityFactory = $imageEntityFactory;
		$this->imageStorageProvider = $imageStorageProvider;
		$this->transactionFactory = $transactionFactory;
		$this->imageStorageName = $imageStorageName;
	}

	/**
	 * @param \SixtyEightPublishers\ImageStorage\Resource\IResourceFactory                         $resourceFactory
	 * @param \Nette\Http\FileUpload                                                               $fileUpload
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions $options
	 *
	 * @return \SixtyEightPublishers\ImageStorage\Resource\IResource
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	protected function createResource(SixtyEightPublishers\ImageStorage\Resource\IResourceFactory $resourceFactory, Nette\Http\FileUpload $fileUpload, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageStorage\Resource\IResource
	{
		$path = sprintf('%s/%s', $options->createNamespace($fileUpload), $options->createSourceName($fileUpload));

		try {
			$resource = $resourceFactory->createResourceFromLocalFile(
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

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator **********/

	/**
	 * {@inheritdoc}
	 */
	public function save(Nette\Http\FileUpload $fileUpload, Options\SaveManipulatorOptions $options): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$storage = $this->imageStorageProvider->get($this->imageStorageName);
		$resource = $this->createResource($storage, $fileUpload, $options);

		try {
			$storage->save($resource);
		} catch (SixtyEightPublishers\ImageStorage\Exception\FilesystemException $e) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('save - resource saving', (string) $resource, 0, $e);
		}

		$transaction = $this->transactionFactory->create(function (Doctrine\ORM\EntityManagerInterface $em, SixtyEightPublishers\ImageStorage\ImageInfo $imageInfo) {
			$image = $this->imageEntityFactory->create(
				SixtyEightPublishers\ImageStorage\DoctrineType\ImageInfo\ImageInfoFactory::create($imageInfo, $this->imageStorageName)
			);

			$em->persist($image);

			return $image;
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
