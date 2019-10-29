<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\DI;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class ImageManagerConfigurationStatementFactory
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\ImageBundle\DI\ImageBundleExtension  */
	private $extension;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\DI\ImageBundleExtension $extension
	 */
	public function __construct(ImageBundleExtension $extension)
	{
		$this->extension = $extension;
	}

	/**
	 * @param string $name
	 * @param array  $options
	 *
	 * @return \Nette\DI\Statement
	 * @throws \Nette\Utils\AssertionException
	 */
	public function create(string $name, array $options): Nette\DI\Statement
	{
		$this->extension->validateConfig(SixtyEightPublishers\ImageBundle\Control\ImageManager\Configuration::DEFAULTS, $options);

		$this->validateIfKeyExists($options, 'storage_class_name', 'string', static function (string $className) {
			if (!is_subclass_of($className, SixtyEightPublishers\ImageBundle\Storage\IDataStorage::class, TRUE)) {
				throw new Nette\Utils\AssertionException('Option "storage_class_name" must be valid class name that implements interface ' . SixtyEightPublishers\ImageBundle\Storage\IDataStorage::class);
			}
		});

		$this->validateIfKeyExists($options, 'manipulators', 'array', function (array $manipulators, array &$options) use ($name) {
			foreach ($manipulators as $key => $manipulator) {
				if (!$this->extension->needRegister($manipulators)) {
					continue;
				}

				$options['manipulators'][$key] = $this->extension->getContainerBuilder()
					->addDefinition($this->extension->prefix('manipulator.' . $name . '.' . $key))
					->setFactory($manipulator)
					->setAutowired(FALSE);
			}
		});

		$this->validateIfKeyExists($options, 'actions', 'array', static function (array $actions, array &$options) {
			foreach ($actions as $key => $action) {
				$options['actions'][$key] = $action instanceof Nette\DI\Statement ? $action : new Nette\DI\Statement($action);
			}
		});

		$this->validateIfKeyExists($options, 'template', 'null|string', function (?string $template, array &$options) {
			if (NULL === $template) {
				$options['template'] = $this->extension->getDefaultTemplates()['image_manager_control'];
			}
		});

		$this->validateIfKeyExists($options, 'max_allowed_images', 'null|int');

		$this->validateIfKeyExists($options, 'max_allowed_images_reached.delete', 'bool');

		$this->validateIfKeyExists($options, 'max_allowed_images_reached.direction', 'string', static function (string $direction) {
			if (!in_array($direction, SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl::DIRECTIONS, TRUE)) {
				throw new Nette\Utils\AssertionException(sprintf(
					'Unsupported value %s for option max_allowed_images_reached.direction',
					$direction
				));
			}
		});

		$this->validateIfKeyExists($options, 'max_file_size', 'null|int|float|string');

		$this->validateIfKeyExists($options, 'save_manipulator_options', 'null|string|' . Nette\DI\Statement::class, static function ($statement, array &$options) {
			$options['save_manipulator_options'] = NULL !== $statement && !$statement instanceof Nette\DI\Statement ? new Nette\DI\Statement($statement) : $statement;
		});

		$this->validateIfKeyExists($options, 'event_subscribers', 'array', function (array $subscribers, array &$options) use ($name) {
			foreach ($subscribers as $key => $subscriber) {
				if (!$this->extension->needRegister($subscriber)) {
					continue;
				}

				$options['event_subscribers'][$key] = $this->extension->getContainerBuilder()
					->addDefinition($this->extension->prefix('event_subscriber.' . $name . '.' . $key))
					->setType(Symfony\Component\EventDispatcher\EventSubscriberInterface::class)
					->setFactory($subscriber)
					->setAutowired(FALSE);
			}
		});
		
		$this->validateIfKeyExists($options, 'thumbnail.preset', 'null|string');

		$this->validateIfKeyExists($options, 'thumbnail.descriptor', 'null|' . Nette\DI\Statement::class);

		$this->validateIfKeyExists($options, 'dropzone.id', 'null|string');

		$this->validateIfKeyExists($options, 'dropzone.template', 'null|string', function (?string $template, array &$options) {
			if (NULL === $template) {
				$options['dropzone']['template'] = $this->extension->getDefaultTemplates()['dropzone_control'];
			}
		});

		$this->validateIfKeyExists($options, 'dropzone.content_html', 'array', function (array $contentHtml, array &$options) {
			foreach ($contentHtml as $key => $html) {
				$options['dropzone']['content_html'][$key] = $html instanceof Nette\DI\Statement ? $html : new Nette\DI\Statement($html);
			}
		});

		$this->validateIfKeyExists($options, 'dropzone.settings', 'array');

		$this->validateIfKeyExists($options, 'dropzone.extensions', 'array');

		return new Nette\DI\Statement(SixtyEightPublishers\ImageBundle\Control\ImageManager\Configuration::class, [
			'options' => $options,
		]);
	}

	/**
	 * @param array         $options
	 * @param string        $key
	 * @param string        $validator
	 * @param callable|NULL $cb
	 *
	 * @throws \Nette\Utils\AssertionException
	 */
	private function validateIfKeyExists(array &$options, string $key, string $validator, ?callable $cb = NULL): void
	{
		try {
			$value = $this->getValue($options, $key);
		} catch (SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException $e) {
			return;
		}

		Nette\Utils\Validators::assert($value, $validator);

		if (is_callable($cb)) {
			$cb($value, $options);
		}
	}

	/**
	 * @param array  $options
	 * @param string $key
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function getValue(array $options, string $key)
	{
		$keys = explode('.', $key);
		$val = $options;

		foreach ($keys as $k) {
			if (!array_key_exists($k, $val)) {
				throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
					'Configuration doesn\'t contains key %s',
					$key
				));
			}

			$val = $val[$k];
		}

		return $val;
	}
}
