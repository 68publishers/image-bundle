<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

trait TExternalAssociationStorageAware
{
	/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage|NULL */
	private $associationStorage;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage
	 *
	 * @return void
	 */
	public function setExternalAssociationStorage(SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage): void
	{
		$this->associationStorage = $externalAssociationStorage;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage|NULL
	 */
	public function getExternalAssociationStorage(): ?SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage
	{
		return $this->associationStorage;
	}
}
