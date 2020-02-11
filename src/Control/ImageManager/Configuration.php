<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\ImageManager;

use Nette;
use SixtyEightPublishers;

final class Configuration
{
	use Nette\SmartObject;

	public const   DEFAULTS = [
		'storage' => [
			'class_name' => SixtyEightPublishers\ImageBundle\Storage\ArrayDataStorage::class,
			'arguments' => [],
		],
		'manipulators' => [],
		'actions' => [],
		'template' => NULL,
		'max_allowed_images' => NULL,
		'max_allowed_images_reached' => [
			'delete' => FALSE,
			'direction' => ImageManagerControl::DIRECTION_TOP,
		],
		'max_file_size' => NULL,
		'save_manipulator_options' => NULL,
		'event_subscribers' => [],
		'thumbnail' => [
			'preset' => NULL,
			'descriptor' => NULL,
		],
		'resource_validators' => [],
		'dropzone' => [
			'id' => NULL,
			'template' => NULL,
			'content_html' => [],
			'settings' => [],
			'extensions' => [],
		],
	];

	/** @var array  */
	private $config;

	/**
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$this->config = Nette\DI\Config\Helpers::merge($options, self::DEFAULTS);
	}

	/**
	 * @param string $key
	 *
	 * @return mixed
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function get(string $key)
	{
		$keys = explode('.', $key);
		$val = $this->config;

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
