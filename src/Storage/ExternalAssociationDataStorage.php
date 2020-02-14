<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class ExternalAssociationDataStorage implements IDataStorage
{
	use Nette\SmartObject;
	use TDataStorage {
		addManipulator as private _addManipulator;
	}

	/** @var \Doctrine\ORM\EntityManagerInterface  */
	private $em;

	/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage  */
	private $externalAssociationStorage;

	/** @var string  */
	private $entityClassName;

	/**
	 * @param \Doctrine\ORM\EntityManagerInterface                                                      $em
	 * @param \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage
	 * @param string|NULL                                                                               $namespace
	 * @param string                                                                                    $entityClassName
	 */
	public function __construct(
		Doctrine\ORM\EntityManagerInterface $em,
		ExternalAssociation\IExternalAssociationStorage $externalAssociationStorage,
		?string $namespace = NULL,
		string $entityClassName = SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class
	) {
		$this->em = $em;
		$this->externalAssociationStorage = $externalAssociationStorage;
		$this->entityClassName = $entityClassName;

		if (NULL !== $namespace) {
			$externalAssociationStorage->setNamespace($namespace);
		}
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage
	 */
	public function getExternalAssociationStorage(): ExternalAssociation\IExternalAssociationStorage
	{
		return $this->externalAssociationStorage;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function addManipulator(Manipulator\IManipulator $manipulator): void
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
