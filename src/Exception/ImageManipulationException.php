<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Exception;

final class ImageManipulationException extends \Exception implements IException
{
	/**
	 * @param string          $manipulationType
	 * @param string          $imageInfoPath
	 * @param int             $code
	 * @param \Throwable|NULL $previous
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException
	 */
	public static function error(string $manipulationType, string $imageInfoPath, int $code = 0, ?\Throwable $previous = NULL): self
	{
		return new static(sprintf(
			'Error during manipulation "%s" on image %s.',
			$manipulationType,
			$imageInfoPath
		), $code, $previous);
	}
}
