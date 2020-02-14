<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation;

use Nette;
use Doctrine;

class ReferenceCollection implements IReferenceCollection
{
	use Nette\SmartObject;

	/** @var \Doctrine\Common\Collections\ArrayCollection  */
	private $inner;

	/**
	 * @param array $references
	 */
	public function __construct(array $references = [])
	{
		$this->inner = new Doctrine\Common\Collections\ArrayCollection($references);
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IReferenceCollection **********/

	/**
	 * {@inheritDoc}
	 */
	public function add(Reference $reference): void
	{
		if (!$this->inner->contains($reference)) {
			$this->inner->add($reference);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepend(Reference $reference): void
	{
		if ($this->inner->contains($reference)) {
			return;
		}

		$items = $this->toArray();

		array_unshift($items, $reference);

		$this->inner = new Doctrine\Common\Collections\ArrayCollection($items);
	}

	/**
	 * {@inheritDoc}
	 */
	public function moveBefore(Reference $reference, Reference $targetReference): void
	{
		$this->move($reference, $targetReference, FALSE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function moveAfter(Reference $reference, Reference $targetReference): void
	{
		$this->move($reference, $targetReference, TRUE);
	}

	/**
	 * {@inheritDoc}
	 */
	public function find(string $id): ?Reference
	{
		/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference $reference */
		foreach ($this as $reference) {
			if ($reference->getId() !== $id) {
				continue;
			}

			return $reference;
		}

		return NULL;
	}

	/**
	 * {@inheritDoc}
	 */
	public function remove(Reference $reference): void
	{
		if ($this->inner->contains($reference)) {
			$this->inner->removeElement($reference);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function map(callable $callback): array
	{
		return array_map($callback, $this->toArray());
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return $this->inner->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIterator(): \Traversable
	{
		return $this->inner->getIterator();
	}

	/**
	 * {@inheritDoc}
	 */
	public function count(): int
	{
		return $this->inner->count();
	}

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference $reference
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference $targetReference
	 * @param bool                                                                    $equal
	 *
	 * @return void
	 */
	private function move(Reference $reference, Reference $targetReference, bool $equal): void
	{
		if (!$this->inner->contains($reference) || !$this->inner->contains($targetReference)) {
			return; # missing references?
		}

		$this->inner->removeElement($reference);

		$index = $this->inner->indexOf($targetReference);

		[$start, $end] = $this->inner->partition(static function ($k) use ($equal, $index) {
			return TRUE === $equal ? $k <= $index : $k < $index;
		});

		$this->inner = new Doctrine\Common\Collections\ArrayCollection(array_merge(
			$start->toArray(),
			[$reference],
			$end->toArray()
		));
	}
}
