<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

interface IExternalAssociationStorageAware
{
	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage
	 *
	 * @return void
	 */
	public function setExternalAssociationStorage(SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage): void;
}
