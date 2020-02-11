<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation;

use Nette;

class SessionAssociationStorage implements IExternalAssociationStorage
{
	use Nette\SmartObject;

	/** @var string  */
	private $key;
	
	/** @var \Nette\Http\Session  */
	private $session;

	/** @var \Nette\Http\SessionSection|NULL */
	private $sessionSection;

	/** @var string|int|\DateTimeInterface  */
	private $expiration = '1 hour';

	/** @var \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\ReferenceCollection|NULL */
	private $collection;

	/**
	 * @param string              $key
	 * @param \Nette\Http\Session $session
	 */
	public function __construct(string $key, Nette\Http\Session $session)
	{
		$this->key = $key;
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
				->getSection(str_replace('\\', '.', static::class) . '/' . $this->key)
				->setExpiration($this->expiration);

			$this->sessionSection->warnOnUndefined = FALSE;
		}

		return $this->sessionSection;
	}

	/********** interface \SixtyEightPublishers\ImageBundle\Storage\ExternalAssociation\IExternalAssociationStorage **********/

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
