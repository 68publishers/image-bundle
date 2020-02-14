<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\ImageManager;

use Nette;
use SixtyEightPublishers;

final class ConfiguredImageManagerControlFactory
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageBundle\Control\ImageManager\IImageManagerControlFactory  */
	private $imageManagerControlFactory;

	/** @var \SixtyEightPublishers\ImageBundle\Storage\IDataStorageFactory  */
	private $dataStorageFactory;

	/** @var \SixtyEightPublishers\ImageBundle\Control\ImageManager\Configuration[]  */
	private $configurations = [];

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Control\ImageManager\IImageManagerControlFactory $imageManagerControlFactory
	 * @param \SixtyEightPublishers\ImageBundle\Storage\IDataStorageFactory                      $dataStorageFactory
	 */
	public function __construct(
		IImageManagerControlFactory $imageManagerControlFactory,
		SixtyEightPublishers\ImageBundle\Storage\IDataStorageFactory $dataStorageFactory
	) {
		$this->imageManagerControlFactory = $imageManagerControlFactory;
		$this->dataStorageFactory = $dataStorageFactory;
	}

	/**
	 * @internal
	 *
	 * @param string                                                               $name
	 * @param \SixtyEightPublishers\ImageBundle\Control\ImageManager\Configuration $configuration
	 *
	 * @return void
	 */
	public function addConfiguration(string $name, Configuration $configuration): void
	{
		$this->configurations[$name] = $configuration;
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerArgs $args
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function create(ConfiguredImageManagerArgs $args): ImageManagerControl
	{
		$config = $this->getConfig($args->getName());
		$control = $this->imageManagerControlFactory->create($this->createDataStorage($args));

		foreach ($config->get('actions') as $action) {
			$control->addAction($action);
		}

		if (is_string($config->get('template'))) {
			$this->setTemplateFile($control, $config->get('template'));
		}

		if (is_int($config->get('max_allowed_images'))) {
			$control->setMaxAllowedImages($config->get('max_allowed_images'));
		}

		$control->setDeleteExistingImageIfMaxAllowedReached(
			(bool) $config->get('max_allowed_images_reached.delete'),
			$config->get('max_allowed_images_reached.direction')
		);

		if (NULL !== $config->get('max_file_size')) {
			$control->setMaxFileSize($config->get('max_file_size'));
		}

		$control->setThumbnailOptions($config->get('thumbnail.preset'), $config->get('thumbnail.descriptor'));

		foreach ($config->get('resource_validators') as $resourceValidator) {
			$control->addResourceValidator($resourceValidator);
		}

		$dispatcher = $control->getEventDispatcher();

		foreach ($config->get('event_subscribers') as $eventSubscriber) {
			$dispatcher->addSubscriber($eventSubscriber);
		}

		$dispatcher->addListener(SixtyEightPublishers\ImageBundle\Event\DropZoneControlSetupEvent::NAME, function (SixtyEightPublishers\ImageBundle\Event\DropZoneControlSetupEvent $event) use ($config) {
			$control = $event->getDropZoneControl();

			if (is_string($config->get('dropzone.id'))) {
				$control->setDropZoneId($config->get('dropzone.id'));
			}

			if (is_string($config->get('dropzone.template'))) {
				$this->setTemplateFile($control, $config->get('dropzone.template'));
			}

			foreach ($config->get('dropzone.content_html') as $contentHtml) {
				$control->addContentHtml($contentHtml);
			}

			foreach ($config->get('dropzone.settings') as $key => $value) {
				$control->addSetting((string) $key, $value);
			}

			foreach ($config->get('dropzone.extensions') as $key => $value) {
				if (!is_array($value)) {
					$key = $value;
					$value = [];
				}

				$control->addExtension((string) $key, $value);
			}
		});

		return $control;
	}

	/**
	 * @param string $name
	 * @param mixed  ...$dataStorageArgs
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerArgs
	 */
	public function createArgs(string $name, ...$dataStorageArgs): ConfiguredImageManagerArgs
	{
		return new ConfiguredImageManagerArgs($name, ...$dataStorageArgs);
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerArgs $args
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Storage\IDataStorage
	 */
	public function createDataStorage(ConfiguredImageManagerArgs $args): SixtyEightPublishers\ImageBundle\Storage\IDataStorage
	{
		$config = $this->getConfig($args->getName());
		$dataStorage = $this->dataStorageFactory->create($config->get('storage.class_name'), ...array_merge($config->get('storage.arguments'), $args->getDataStorageArgs()));

		foreach ($config->get('manipulators') as $manipulator) {
			$dataStorage->addManipulator($manipulator);
		}

		foreach (array_merge($config->get('storage.options'), $args->getOptions()) as $key => $options) {
			$dataStorage->getOptions()->set($key, $options);
		}

		return $dataStorage;
	}

	/**
	 * @param \SixtyEightPublishers\SmartNetteComponent\UI\Control $control
	 * @param string                                               $file
	 *
	 * @return void
	 */
	private function setTemplateFile(SixtyEightPublishers\SmartNetteComponent\UI\Control $control, string $file): void
	{
		if (Nette\Utils\Strings::startsWith($file, '@')) {
			$control->setRelativeFile($file);
		} else {
			$control->setFile($file);
		}
	}

	/**
	 * @param string $name
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\Configuration
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	private function getConfig(string $name): Configuration
	{
		if (!array_key_exists($name, $this->configurations)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Missing definition for ImageManagerControl with name %s.',
				$name
			));
		}

		return $this->configurations[$name];
	}
}
