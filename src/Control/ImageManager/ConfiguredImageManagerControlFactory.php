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
	private $definitions = [];

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
	public function addDefinition(string $name, Configuration $configuration): void
	{
		$this->definitions[$name] = $configuration;
	}

	/**
	 * @param string $name
	 * @param mixed  ...$dataStorageArgs
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl
	 */
	public function create(string $name, ...$dataStorageArgs): ImageManagerControl
	{
		if (!array_key_exists($name, $this->definitions)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Missing definition for ImageManagerControl with name %s.',
				$name
			));
		}

		$config = $this->definitions[$name];
		$dataStorage = $this->dataStorageFactory->create($config->get('storage.class_name'), ...array_merge($config->get('storage.arguments'), $dataStorageArgs));

		foreach ($config->get('manipulators') as $manipulator) {
			$dataStorage->addManipulator($manipulator);
		}

		foreach ($config->get('storage.metadata') as $key => $metadata) {
			$dataStorage->getMetadata()->set($key, $metadata);
		}

		$control = $this->imageManagerControlFactory->create($dataStorage);

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

		if ($config->get('save_manipulator_options') instanceof SixtyEightPublishers\ImageBundle\Storage\Manipulator\Options\SaveManipulatorOptions) {
			$control->setSaveManipulatorOptions($config->get('save_manipulator_options'));
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
}
