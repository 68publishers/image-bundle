<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DI;

use Kdyby;
use Nette;
use Symfony;
use SixtyEightPublishers;

final class ImageBundleExtension extends Nette\DI\CompilerExtension implements
	Kdyby\Doctrine\DI\IEntityProvider,
	Kdyby\Doctrine\DI\ITargetEntityProvider,
	Kdyby\Translation\DI\ITranslationProvider
{
	/** @var array  */
	private $defaults = [
		'image_entity' => SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic\Image::class, # or array [entity => class name, mapping: array]
		'image_entity_factory' => NULL,
		'resource_metadata_factory' => SixtyEightPublishers\ImageBundle\ResourceMetadata\ResourceMetadataFactory::class,
		'data_storage_factory' => SixtyEightPublishers\ImageBundle\Storage\DataStorageFactory::class,
		'image_managers' => [],
		'templates' => [
			'image_manager_control' => NULL,
			'dropzone_control' => NULL,
		],
	];

	/** @var array|NULL */
	private $imageEntityDefinition;

	/** @var array|NULL */
	private $defaultTemplates;

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	public function loadConfiguration(): void
	{
		$this->validateConfig($this->defaults);

		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		Nette\Utils\Validators::assertField($config, 'image_managers', 'array[]');

		$this->registerImageEntityFactory($builder, $config);
		$this->registerResourceMetadataFactory($builder, $config);
		$this->registerDataStorageFactory($builder, $config);

		$builder->addDefinition($this->prefix('event_subscriber.delete_image_source'))
			->setType(SixtyEightPublishers\ImageBundle\EventSubscriber\DeleteImageSourceEventSubscriber::class);

		$builder->addDefinition($this->prefix('control.image_manager_control_factory'))
			->setImplement(SixtyEightPublishers\ImageBundle\Control\ImageManager\IImageManagerControlFactory::class)
			->setArguments([
				'eventDispatcher' => new Nette\DI\Statement(Symfony\Component\EventDispatcher\EventDispatcher::class),
			]);

		$builder->addDefinition($this->prefix('control.dropzone_control_factory'))
			->setImplement(SixtyEightPublishers\ImageBundle\Control\DropZone\IDropZoneControlFactory::class);

		$configurableImageManagerControlFactory = $builder->addDefinition($this->prefix('control.configurable_image_manager_control_factory'))
			->setType(SixtyEightPublishers\ImageBundle\Control\ImageManager\ConfiguredImageManagerControlFactory::class);

		$configurationStatementFactory = new ImageManagerConfigurationStatementFactory($this);

		foreach ($config['image_managers'] as $name => $options) {
			$configurableImageManagerControlFactory->addSetup('addDefinition', [
				'name' => (string) $name,
				'configuration' => $configurationStatementFactory->create((string) $name, $options),
			]);
		}
	}

	/**
	 * @return array
	 * @throws \Nette\Utils\AssertionException
	 */
	public function getDefaultTemplates(): array
	{
		if (is_array($this->defaultTemplates)) {
			return $this->defaultTemplates;
		}

		$config = $this->getConfig($this->defaults);

		Nette\Utils\Validators::assertField($config, 'templates', 'array');
		Nette\Utils\Validators::assertField($config['templates'], 'image_manager_control', 'null|string');
		Nette\Utils\Validators::assertField($config['templates'], 'dropzone_control', 'null|string');

		return $this->defaultTemplates = $config['templates'];
	}

	/**
	 * @param mixed $what
	 *
	 * @return bool
	 */
	public function needRegister($what): bool
	{
		return (!is_string($what) || !Nette\Utils\Strings::startsWith($what, '@'));
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 * @param array                      $config
	 *
	 * @return void
	 * @throws \Nette\Utils\AssertionException
	 */
	private function registerImageEntityFactory(Nette\DI\ContainerBuilder $builder, array $config): void
	{
		Nette\Utils\Validators::assertField($config, 'image_entity_factory', 'null|string|' . Nette\DI\Statement::class);

		$imageEntity = $this->getImageEntityDefinition()['entity'];
		$imageEntityFactory = $config['image_entity_factory'];

		if (NULL === $imageEntityFactory) {
			switch ($imageEntity) {
				case SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic\Image::class:
					$imageEntityFactory = SixtyEightPublishers\ImageBundle\EntityFactory\DefaultImageEntityFactory::class;

					break;
				case SixtyEightPublishers\ImageBundle\DoctrineEntity\SoftDeletable\Image::class:
					$imageEntityFactory = SixtyEightPublishers\ImageBundle\EntityFactory\SoftDeletableImageEntityFactory::class;

					break;
				default:
					throw new Nette\Utils\AssertionException(sprintf(
						'You have custom Image entity %s, please provide your own implementation of %s via option %s.image_entity_factory',
						$imageEntity,
						SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory::class,
						$this->name
					));
			}
		}

		if ($this->needRegister($imageEntityFactory)) {
			$builder->addDefinition($this->prefix('image_entity_factory'))
				->setType(SixtyEightPublishers\ImageBundle\EntityFactory\IImageEntityFactory::class)
				->setFactory($imageEntityFactory);
		}
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 * @param array                      $config
	 *
	 * @return void
	 * @throws \Nette\Utils\AssertionException
	 */
	private function registerResourceMetadataFactory(Nette\DI\ContainerBuilder $builder, array $config): void
	{
		Nette\Utils\Validators::assertField($config, 'resource_metadata_factory', 'string|' . Nette\DI\Statement::class);

		$resourceMetadataFactory = $config['resource_metadata_factory'];

		if ($this->needRegister($resourceMetadataFactory)) {
			$builder->addDefinition($this->prefix('resource_metadata_factory'))
				->setType(SixtyEightPublishers\ImageBundle\ResourceMetadata\IResourceMetadataFactory::class)
				->setFactory($resourceMetadataFactory);
		}
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $builder
	 * @param array                      $config
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	private function registerDataStorageFactory(Nette\DI\ContainerBuilder $builder, array $config): void
	{
		Nette\Utils\Validators::assertField($config, 'data_storage_factory', 'string|' . Nette\DI\Statement::class);

		$dataStorageFactory = $config['data_storage_factory'];

		if ($this->needRegister($dataStorageFactory)) {
			$builder->addDefinition($this->prefix('data_storage_factory'))
				->setType(SixtyEightPublishers\ImageBundle\Storage\IDataStorageFactory::class)
				->setFactory($dataStorageFactory);
		}
	}

	/**
	 * @return array
	 * @throws \Nette\Utils\AssertionException
	 */
	private function getImageEntityDefinition(): array
	{
		if (is_array($this->imageEntityDefinition)) {
			return $this->imageEntityDefinition;
		}

		$config = $this->getConfig($this->defaults);

		Nette\Utils\Validators::assertField($config, 'image_entity', 'string|array');

		$imageEntity = array_merge([
			'entity' => NULL,
			'mapping' => [],
		], is_array($config['image_entity']) ? $config['image_entity'] : [ 'entity' => $config['image_entity'] ]);

		Nette\Utils\Validators::assertField($imageEntity, 'entity', 'string');
		Nette\Utils\Validators::assertField($imageEntity, 'mapping', 'array');

		if (!is_subclass_of($imageEntity['entity'], SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class, TRUE)) {
			throw new Nette\Utils\AssertionException(sprintf(
				'Image entity must implements interface %s.',
				SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class
			));
		}

		return $this->imageEntityDefinition = $imageEntity;
	}

	/**************** interface \Kdyby\Doctrine\DI\IEntityProvider ****************/

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	public function getEntityMappings(): array
	{
		$entity = $this->getImageEntityDefinition()['entity'];

		switch ($entity) {
			case SixtyEightPublishers\ImageBundle\DoctrineEntity\Basic\Image::class:
				return [
					'SixtyEightPublishers\ImageBundle\DoctrineEntity' => __DIR__ . '/../DoctrineEntity/Basic',
				];
			case SixtyEightPublishers\ImageBundle\DoctrineEntity\SoftDeletable\Image::class:
				return [
					'SixtyEightPublishers\ImageBundle\DoctrineEntity' => __DIR__ . '/../DoctrineEntity/SoftDeletable',
				];
		}

		return [];
	}

	/**************** interface \Kdyby\Doctrine\DI\ITargetEntityProvider ****************/

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	public function getTargetEntityMappings(): array
	{
		[ $entity, $mapping ] = array_values($this->getImageEntityDefinition());
		$mapping['targetEntity'] = $entity;

		$targetEntities = [
			SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class => $mapping,
		];

		if (is_subclass_of($entity, SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage::class, TRUE)) {
			$targetEntities[SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage::class] = $mapping;
		}

		return $targetEntities;
	}

	/******************* interface \Kdyby\Translation\DI\ITranslationProvider *******************/

	/**
	 * {@inheritdoc}
	 */
	public function getTranslationResources(): array
	{
		return [
			__DIR__ . '/../locale',
		];
	}
}
