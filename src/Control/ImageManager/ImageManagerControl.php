<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\ImageManager;

use Nette;
use Symfony;
use Doctrine;
use SixtyEightPublishers;

final class ImageManagerControl extends SixtyEightPublishers\SmartNetteComponent\UI\Control implements SixtyEightPublishers\SmartNetteComponent\Translator\ITranslatorAware
{
	use SixtyEightPublishers\SmartNetteComponent\Translator\TTranslatorAware;

	public const    DIRECTION_TOP = 'top',
					DIRECTION_BOTTOM = 'bottom';

	public const    DIRECTIONS = [
		self::DIRECTION_TOP,
		self::DIRECTION_BOTTOM,
	];

	/** @var \SixtyEightPublishers\ImageBundle\Storage\IDataStorage  */
	private $storage;

	/** @var \SixtyEightPublishers\ImageBundle\Control\DropZone\IDropZoneControlFactory  */
	private $dropZoneControlFactory;

	/** @var \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  */
	private $transactionFactory;

	/** @var \Symfony\Component\EventDispatcher\EventDispatcher|\Symfony\Component\EventDispatcher\EventDispatcherInterface  */
	private $eventDispatcher;

	/** @var int */
	private $maxFileSize;

	/** @var int  */
	private $maxAllowedImages = 100;

	/** @var \SixtyEightPublishers\ImageBundle\Action\IAction[] */
	private $actions = [];

	/** @var array  */
	private $deleteExistingImageIfMaxAllowedReached = [ FALSE, self::DIRECTION_TOP ];

	/** @var \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[]|NULL */
	private $images;

	/** @var \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions|NULL */
	private $saveManipulatorOptions;

	/** @var string|NULL */
	private $thumbnailPreset;

	/** @var \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor|NULL */
	private $thumbnailDescriptor;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\IDataStorage                     $dataStorage
	 * @param \SixtyEightPublishers\ImageBundle\Control\DropZone\IDropZoneControlFactory $dropZoneControlFactory
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory  $transactionFactory
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface                $eventDispatcher
	 */
	public function __construct(
		SixtyEightPublishers\ImageBundle\Storage\IDataStorage $dataStorage,
		SixtyEightPublishers\ImageBundle\Control\DropZone\IDropZoneControlFactory $dropZoneControlFactory,
		SixtyEightPublishers\DoctrinePersistence\Transaction\ITransactionFactory $transactionFactory,
		?Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher = NULL
	) {
		parent::__construct();

		$this->storage = $dataStorage;
		$this->dropZoneControlFactory = $dropZoneControlFactory;
		$this->transactionFactory = $transactionFactory;
		$this->eventDispatcher = $eventDispatcher ?? new Symfony\Component\EventDispatcher\EventDispatcher();
		$this->maxFileSize = SixtyEightPublishers\ImageBundle\Helper\MaxFileSize::getDefault();
	}

	/**
	 * @param string $actionName
	 * @param string $id
	 *
	 * @return void
	 */
	public function handleDoAction(string $actionName, string $id): void
	{
		try {
			$action = $this->getValidAction($actionName);
			$image = $this->findImage($id);

			$action->run($this->getStorage(), $image);
		} catch (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
			$this->eventDispatcher->dispatch(
				new SixtyEightPublishers\ImageBundle\Event\ActionErrorEvent($actionName, $e),
				SixtyEightPublishers\ImageBundle\Event\ActionErrorEvent::NAME
			);

			return;
		}

		$this->redrawUpload();
		$this->redrawImages();
		$this->eventDispatcher->dispatch(
			new SixtyEightPublishers\ImageBundle\Event\ActionSuccessEvent($actionName, $image),
			SixtyEightPublishers\ImageBundle\Event\ActionSuccessEvent::NAME
		);
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions $saveManipulatorOptions
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function setSaveManipulatorOptions(SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions $saveManipulatorOptions): self
	{
		$this->saveManipulatorOptions = $saveManipulatorOptions;

		return $this;
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Action\IAction $action
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function addAction(SixtyEightPublishers\ImageBundle\Action\IAction $action): self
	{
		if (!$action->canBeUsed($this->storage)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Action %s (%s) can\'t be used with current DataStorage.',
				$action->getName(),
				get_class($action)
			));
		}

		$this->actions[] = $action;

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Storage\IDataStorage
	 */
	public function getStorage(): SixtyEightPublishers\ImageBundle\Storage\IDataStorage
	{
		return $this->storage;
	}

	/**
	 * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
	 */
	public function getEventDispatcher(): Symfony\Component\EventDispatcher\EventDispatcherInterface
	{
		return $this->eventDispatcher;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render(): void
	{
		$maxFiles = $this->getMaxFiles();

		$this->template->setTranslator($this->getPrefixedTranslator());

		$this->template->images = $this->getImages();
		$this->template->maxAllowedImages = $this->maxAllowedImages;
		$this->template->uniqueId = $this->getUniqueId();
		$this->template->isSaveable = $this->storage->hasManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISaveManipulator::class);
		$this->template->actions = $this->actions;
		$this->template->allowUpload = $allowUpload = (NULL === $maxFiles || $maxFiles > 0);
		$this->template->denyUpload = !$allowUpload;
		$this->template->thumbnailPreset = $this->thumbnailPreset;
		$this->template->thumbnailDescriptor = $this->thumbnailDescriptor;

		$this->doRender();
	}

	/**
	 * @param int $maxAllowedImages
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function setMaxAllowedImages(int $maxAllowedImages): self
	{
		$this->maxAllowedImages = $maxAllowedImages;

		return $this;
	}

	/**
	 * @param bool   $delete
	 * @param string $direction
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function setDeleteExistingImageIfMaxAllowedReached(bool $delete, string $direction = self::DIRECTION_TOP): self
	{
		if (TRUE === $delete) {
			# check manipulator only
			$this->getStorage()->getManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator::class);
		}

		if (!in_array($direction, self::DIRECTIONS, TRUE)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Direction %s is not supported.',
				$direction
			));
		}

		$this->deleteExistingImageIfMaxAllowedReached = [
			$delete,
			$direction,
		];

		return $this;
	}

	/**
	 * @param int|string $maxFileSize
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function setMaxFileSize($maxFileSize): self
	{
		$this->maxFileSize = SixtyEightPublishers\ImageBundle\Helper\MaxFileSize::parseBytes($maxFileSize);

		return $this;
	}

	/**
	 * @param string|NULL                                                               $preset
	 * @param \SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor|NULL $descriptor
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function setThumbnailOptions(?string $preset, ?SixtyEightPublishers\ImageStorage\Responsive\Descriptor\IDescriptor $descriptor = NULL): self
	{
		$this->thumbnailPreset = $preset;
		$this->thumbnailDescriptor = $descriptor;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function createPrefixedTranslatorDomain(): string
	{
		return str_replace('\\', '_', static::class);
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidStateException
	 */
	protected function createComponentDropZone(): SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	{
		if (!$this->storage->hasManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISaveManipulator::class)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'DataStorage must contains manipulator with type %s if you want to use DropZone.',
				SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISaveManipulator::class
			));
		}

		$dropZone = $this->dropZoneControlFactory->create($this->eventDispatcher);
		$translator = $this->getPrefixedTranslator();

		# defaults
		$dropZone->setSettings([
			'previewTemplate' => '', # we have custom list of images
			'previewsContainer' => FALSE,
			'createImageThumbnails' => FALSE,
			'maxFilesize' => $this->maxFileSize / 1024 / 1024,
			'dictFileTooBig' => $translator->translate('message.dict_file_too_big'),
			'dictInvalidFileType' => $translator->translate('message.dict_invalid_file_type'),
			'dictResponseError' => $translator->translate('message.dict_response_error'),
			'dictMaxFilesExceeded' => $translator->translate('message.dict_max_files_exceeded'),
		]);

		if (NULL !== ($maxFiles = $this->getMaxFiles())) {
			$dropZone->addSetting('maxFiles', $maxFiles);
		}

		$dropZone->setAccepted(['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

		$this->eventDispatcher->addListener(SixtyEightPublishers\ImageBundle\Event\UploadCompletedEvent::NAME, function () {
			$this->redrawUpload();
		});

		$this->eventDispatcher->addListener(SixtyEightPublishers\ImageBundle\Event\FileUploadEvent::NAME, function (SixtyEightPublishers\ImageBundle\Event\FileUploadEvent $event) {
			$image = $this->uploadImage($event->getFileUpload());

			$this->redrawImages();
			$this->eventDispatcher->dispatch(
				new SixtyEightPublishers\ImageBundle\Event\ImageUploadedEvent($image),
				SixtyEightPublishers\ImageBundle\Event\ImageUploadedEvent::NAME
			);
		});

		$this->eventDispatcher->dispatch(
			new SixtyEightPublishers\ImageBundle\Event\DropZoneControlSetupEvent($dropZone),
			SixtyEightPublishers\ImageBundle\Event\DropZoneControlSetupEvent::NAME
		);

		return $dropZone;
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\UploadException
	 * @throws \Throwable
	 */
	private function uploadImage(Nette\Http\FileUpload $fileUpload): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		if ($this->getImages()->count() >= $this->maxAllowedImages) {
			[$delete, $direction] = $this->deleteExistingImageIfMaxAllowedReached;

			if (FALSE === $delete) {
				throw SixtyEightPublishers\ImageBundle\Exception\UploadException::maximumFilesReached($this->maxAllowedImages);
			}

			return $this->doImageUploadWithDeletion($fileUpload, $direction);
		}

		return $this->doImageUpload($fileUpload);
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	private function doImageUpload(Nette\Http\FileUpload $fileUpload): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		/** @var \SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISaveManipulator $manipulator */
		$manipulator = $this->storage->getManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\ISaveManipulator::class);

		return $manipulator->save($fileUpload, $this->saveManipulatorOptions ?? new SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions());
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 * @param string                 $direction
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 * @throws \Throwable
	 */
	private function doImageUploadWithDeletion(Nette\Http\FileUpload $fileUpload, string $direction): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$images = $this->getImages();
		$imageForDelete = self::DIRECTION_TOP === $direction ? $images->first() : $images->last();

		if (!$imageForDelete instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage) {
			return $this->doImageUpload($fileUpload);
		}

		$transaction = $this->transactionFactory->create(function ($_, Nette\Http\FileUpload $fileUpload, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $imageForDelete) {
			/** @var \SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator $deleteManipulator */
			$deleteManipulator = $this->getStorage()->getManipulator(SixtyEightPublishers\ImageBundle\Storage\Manipulator\IDeleteManipulator::class);

			$deleteManipulator->delete($imageForDelete);

			return $this->doImageUpload($fileUpload);
		});

		$transaction->error(static function (SixtyEightPublishers\DoctrinePersistence\Exception\PersistenceException $e) {
			throw SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('upload new and delete existing', '?', 0, $e);
		});

		return $transaction->run($fileUpload, $imageForDelete);
	}

	/**
	 * @return void
	 */
	private function redrawUpload(): void
	{
		$this->redrawControl('upload');
	}

	/**
	 * @return void
	 */
	private function redrawImages(): void
	{
		$this->images = NULL;
		$this->redrawControl('images');
	}

	/**
	 * @return \Doctrine\Common\Collections\Collection|\SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage[]
	 */
	private function getImages(): Doctrine\Common\Collections\Collection
	{
		return $this->images ?: $this->images = $this->storage->getImages();
	}

	/**
	 * @return int|NULL
	 */
	private function getMaxFiles(): ?int
	{
		if (NULL === $this->maxAllowedImages) {
			return NULL;
		}

		if (TRUE === $this->deleteExistingImageIfMaxAllowedReached[0]) {
			return $this->maxAllowedImages;
		}

		$max = $this->maxAllowedImages - $this->getImages()->count();

		return 0 < $max ? $max : 0;
	}

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Action\IAction
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidStateException
	 */
	private function getValidAction(string $name): SixtyEightPublishers\ImageBundle\Action\IAction
	{
		$found = NULL;
		foreach ($this->actions as $action) {
			if ($action->getName() === $name) {
				$found = $action;

				break;
			}
		}

		if (NULL === $found) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Missing action with name %s.',
				$name
			));
		}

		if (!$found->canBeUsed($this->getStorage())) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Action %s can\'t be used with current DataStorage.',
				$name
			));
		}

		return $found;
	}

	/**
	 * @param string $id
	 *
	 * @return \SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidStateException
	 */
	private function findImage(string $id): SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage
	{
		$image = $this->getImages()->filter(static function (SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image) use ($id) {
			return (string) $image->getId() === $id;
		})->first();

		if (!$image instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Image with ID %s not found in DataStorage.',
				$id
			));
		}

		return $image;
	}
}
