<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;
use SixtyEightPublishers;

final class DoctrineDataStorage implements IDataStorage
{
	use Nette\SmartObject,
		TDataStorage;

	/** @var \Doctrine\ORM\QueryBuilder  */
	protected $qb;

	/**
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	public function __construct(Doctrine\ORM\QueryBuilder $qb)
	{
		$this->qb = $qb;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getImages(): Doctrine\Common\Collections\Collection
	{
		$collection = new Doctrine\Common\Collections\ArrayCollection();

		foreach (new Doctrine\ORM\Tools\Pagination\Paginator($this->qb->getQuery(), FALSE) as $image) {
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
