<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage;

use Nette;
use Doctrine;

final class CallbackDataStorage implements IDataStorage
{
	use Nette\SmartObject,
		TManipulators;

	/** @var callable  */
	protected $callback;

	/**
	 * @param callable $callback
	 */
	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}

	/*************** interface \SixtyEightPublishers\ImageBundle\Storage\IDataStorage ***************/

	/**
	 * {@inheritdoc}
	 */
	public function getImages(): Doctrine\Common\Collections\Collection
	{
		$cb = $this->callback;

		return (new ArrayDataStorage((array) $cb()))->getImages();
	}
}
