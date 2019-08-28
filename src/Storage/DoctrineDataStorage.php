<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class DoctrineDataStorage implements IDataStorage
{
	use Nette\SmartObject,
		TManipulators;

	/** @var \Doctrine\ORM\QueryBuilder  */
	protected $qb;

	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	public function __construct(Doctrine\ORM\QueryBuilder $qb)
	{
		$this->qb = $qb;
	}

	/**
	 * @return \Doctrine\ORM\Query\Expr\From|NULL
	 */
	private function getFrom(): ?Doctrine\ORM\Query\Expr\From
	{
		$from = $this->qb->getDQLPart('from');

		return isset($from[0]) && $from[0] instanceof Doctrine\ORM\Query\Expr\From ? $from[0] : NULL;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getImages(): Doctrine\Common\Collections\Collection
	{
		$qb = clone $this->qb;
		$from = $this->getFrom();

		# remove soft-deleted images
		if (NULL !== $from && (is_a($from->getFrom(), SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage::class, TRUE) || is_subclass_of($from->getFrom(), SixtyEightPublishers\ImageBundle\DoctrineEntity\ISoftDeletableImage::class, TRUE))) {
			$qb->andWhere($from->getAlias() . '.deleted = :__deleted')
				->setParameter('__deleted', FALSE);
		}

		$collection = new Doctrine\Common\Collections\ArrayCollection();

		foreach (new Doctrine\ORM\Tools\Pagination\Paginator($qb->getQuery(), FALSE) as $image) {
			if (!$image instanceof SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage) {
				throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
					'Invalid entities returned from passed Query. Entities must be instances of interface %s, %s given.',
					SixtyEightPublishers\ImageBundle\DoctrineEntity\IImage::class,
					get_class($image)
				));
			}

			$collection->add($image);
		}

		return $collection;
	}
}
