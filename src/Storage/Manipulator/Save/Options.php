<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Save;

use Nette;
use Ramsey;
use SixtyEightPublishers;

final class Options
{
	use Nette\SmartObject;

	public const    NAMESPACE = 'save_manipulator.namespace',
					SOURCE_NAME_CALLBACK = 'save_manipulator.source_name_callback',
					CUSTOM_RESOURCE_METADATA = 'save_manipulator.custom_resource_metadata',
					TRANSACTION_EXTENSION_CALLBACK = 'save_manipulator.transaction_extension_callback';

	/** @var \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions  */
	private $options;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options
	 */
	public function __construct(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options)
	{
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function getNamespace(): string
	{
		return $this->options->has(self::NAMESPACE, 'string') ? $this->options->get(self::NAMESPACE) : '';
	}

	/**
	 * @param \Nette\Http\FileUpload $fileUpload
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function getSourceName(Nette\Http\FileUpload $fileUpload): string
	{
		if ($this->options->has(self::SOURCE_NAME_CALLBACK, 'callable')) {
			$cb = $this->options->get(self::SOURCE_NAME_CALLBACK);

			return (string) $cb($fileUpload);
		}

		return Ramsey\Uuid\Uuid::uuid4()->toString() . '.' . Nette\Utils\Strings::lower(pathinfo($fileUpload->getName(), PATHINFO_EXTENSION));
	}

	/**
	 * @return array
	 */
	public function getCustomMetadata(): array
	{
		return $this->options->has(self::CUSTOM_RESOURCE_METADATA, 'array') ? $this->options->get(self::CUSTOM_RESOURCE_METADATA) : [];
	}

	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction
	 *
	 * @return void
	 */
	public function extendTransaction(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction): void
	{
		if ($this->options->has(self::TRANSACTION_EXTENSION_CALLBACK, 'callable')) {
			$cb = $this->options->get(self::TRANSACTION_EXTENSION_CALLBACK);

			$cb($transaction);
		}
	}
}
