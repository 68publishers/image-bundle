<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable;

use SixtyEightPublishers;

class FlaggableManipulator extends SixtyEightPublishers\ImageBundle\Storage\Manipulator\AbstractManipulator implements IFlaggableManipulator, SixtyEightPublishers\ImageBundle\Storage\Manipulator\IExternalAssociationStorageAware
{
	use SixtyEightPublishers\ImageBundle\Storage\Manipulator\TAssociationStorageAware;

	/** @var callable[]  */
	private $handlers = [];

	/**
	 * @param array $handlers
	 */
	public function __construct(array $handlers = [])
	{
		foreach ($handlers as $flag => $handler) {
			$this->addHandler((string) $flag, $handler);
		}
	}

	/**
	 * @param string   $flag
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function addHandler(string $flag, callable $callback): void
	{
		$this->handlers[$flag] = $callback;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\Manipulator\Flaggable\IFlaggableManipulator **********/

	/**
	 * {@inheritDoc}
	 */
	public function isFlagSupported(string $flag): bool
	{
		return isset($this->handlers[$flag]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function __invoke(SixtyEightPublishers\ImageBundle\Storage\Options\IOptions $options, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $image, string $flag, bool $unique = FALSE): void
	{
		if (!$this->isFlagSupported($flag)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Missing handler for flag "%s".',
				$flag
			));
		}

		$this->handlers[$flag]($options, $image, $unique);

		$associationStorage = $this->getExternalAssociationStorage();

		if (NULL === $associationStorage) {
			return;
		}

		$references = $associationStorage->getReferences();
		$selectedReference = $references->find((string) $image->getId());

		if (NULL === $selectedReference) {
			return;
		}

		if (TRUE === $unique) {
			/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference $reference */
			foreach ($references as $reference) {
				$reference->removeMetadata($flag);
			}
		}

		$selectedReference->addMetadata($flag, TRUE);
		$associationStorage->flush();
	}
}
