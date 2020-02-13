<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Exception;

final class ImageManipulationException extends \Exception implements IException
{
	/**
	 * @param string          $manipulationType
	 * @param int             $code
	 * @param \Throwable|NULL $previous
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public static function error(string $manipulationType, int $code = 0, ?\Throwable $previous = NULL): self
	{
		return new static(sprintf(
			'Error during manipulation %s on image.',
			$manipulationType
		), $code, $previous);
	}
}
