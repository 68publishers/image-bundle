<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation;

interface IExternalAssociationStorage
{
	/**
	 * @param string $namespace
	 *
	 * @return void
	 */
	public function setNamespace(string $namespace): void;

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IReferenceCollection
	 */
	public function getReferences(): IReferenceCollection;

	/**
	 * Save collection into storage
	 */
	public function flush(): void;

	/**
	 * Clean & Destroy storage section
	 *
	 * @return void
	 */
	public function clean(): void;
}
