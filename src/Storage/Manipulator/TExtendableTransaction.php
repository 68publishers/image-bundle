<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Storage\Manipulator;

use SixtyEightPublishers;

trait TExtendableTransaction
{
	/**
	 * @param \SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction
	 *
	 * @return void
	 */
	protected function extendTransaction(SixtyEightPublishers\DoctrinePersistence\Transaction\ITransaction $transaction): void
	{
	}
}
