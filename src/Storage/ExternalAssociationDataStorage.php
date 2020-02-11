<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class ExternalAssociationDataStorage implements IDataStorage
{
	use Nette\SmartObject;
	use TManipulators {
		addManipulator as private _addManipulator;
	}

	/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage  */
	private $externalAssociationStorage;

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/** @var string  */
	private $entityClassName;

	/**
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage
	 * @param \Doctrine\ORM\EntityManagerInterface                                                      $em
	 * @param string                                                                                    $entityClassName
	 */
	public function __construct(SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage, Doctrine\ORM\EntityManagerInterface $em, string $entityClassName = SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class)
	{
		$this->externalAssociationStorage = $externalAssociationStorage;
		$this->em = $em;
		$this->entityClassName = $entityClassName;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator($manipulator): void
	{
		if ($manipulator instanceof Manipulator\IExternalAssociationStorageAware) {
			$manipulator->setExternalAssociationStorage($this->externalAssociationStorage);
		}

		$this->_addManipulator($manipulator);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws \Doctrine\ORM\Mapping\MappingException
	 */
	public function getImages(): Doctrine\Common\Collections\Collection
	{
		$qb = $this->em->createQueryBuilder();
		$cm = $this->em->getClassMetadata($this->entityClassName);

		$ids = $this->externalAssociationStorage->getReferences()->map(static function (SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\Reference $reference) {
			return $reference->getId();
		});

		if (0 >= count($ids)) {
			return new Doctrine\Common\Collections\ArrayCollection();
		}

		$qb->select('i')
			->from($this->entityClassName, 'i')
			->where($qb->expr()->in('i.' . $cm->getSingleIdentifierFieldName(), $ids));

		$images = (new DoctrineDataStorage($qb))->getImages()->toArray();

		usort($images, static function (SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $first, SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage $second) use ($ids) {
			return array_search((string) $first->getId(), $ids, TRUE) - array_search((string) $second->getId(), $ids, TRUE);
		});

		return new Doctrine\Common\Collections\ArrayCollection($images);
	}
}
