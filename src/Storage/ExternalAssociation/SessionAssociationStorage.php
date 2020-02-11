<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation;

use Nette;
use SixtyEightPublishers;

class SessionAssociationStorage implements IExternalAssociationStorage
{
	use Nette\SmartObject;
	
	/** @var \Nette\Http\Session  */
	private $session;

	/** @var \Nette\Http\SessionSection|NULL */
	private $sessionSection;

	/** @var string|NULL */
	private $namespace;

	/** @var string|int|\DateTimeInterface  */
	private $expiration = '1 hour';

	/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\ReferenceCollection|NULL */
	private $collection;

	/**
	 * @param \Nette\Http\Session $session
	 */
	public function __construct(Nette\Http\Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @param string|int|\DateTimeInterface $expiration
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\SessionAssociationStorage
	 */
	public function setExpiration($expiration): self
	{
		$this->expiration = $expiration;

		return $this;
	}

	/**
	 * @return \Nette\Http\SessionSection
	 */
	private function getSection(): Nette\Http\SessionSection
	{
		if (NULL === $this->sessionSection) {
			$this->sessionSection = $this->session
				->getSection(str_replace('\\', '.', static::class) . (NULL === $this->namespace ? '' : '//' . $this->namespace))
				->setExpiration($this->expiration);

			$this->sessionSection->warnOnUndefined = FALSE;
		}

		return $this->sessionSection;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage **********/

	/**
	 * {@inheritDoc}
	 */
	public function setNamespace(string $namespace): void
	{
		if (NULL !== $this->sessionSection) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException('A namespace can￿\'t be changed if a session section was already created.');
		}

		$this->namespace = $namespace;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getReferences(): IReferenceCollection
	{
		if (NULL === $this->collection) {
			$this->collection = new ReferenceCollection($this->getSection()['references'] ?? []);
		}

		return $this->collection;
	}

	/**
	 * {@inheritDoc}
	 */
	public function flush(): void
	{
		if (NULL === $this->collection) {
			return;
		}

		$this->getSection()['references'] = $this->collection->toArray();
	}

	/**
	 * {@inheritDoc}
	 */
	public function clean(): void
	{
		$this->collection = NULL;
		$this->getSection()->remove();
	}
}
