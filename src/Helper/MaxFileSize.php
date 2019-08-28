<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Helper;

use Nette;
use SixtyEightPublishers;

final class MaxFileSize
{
	use Nette\StaticClass;

	/**
	 * @return int
	 */
	public static function getDefault(): int
	{
		return self::parseBytes(ini_get('upload_max_filesize'));
	}

	/**
	 * @param int|float|string $value
	 *
	 * @return int
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public static function parseBytes($value): int
	{
		if (is_numeric($value)) {
			return (int) $value;
		}

		$value = Nette\Utils\Strings::trim($value);
		$numValue = Nette\Utils\Strings::substring($value, 0, Nette\Utils\Strings::length($value) - 1);

		switch (Nette\Utils\Strings::lower(Nette\Utils\Strings::substring($value, Nette\Utils\Strings::length($value) - 1))) {
			case 'g':
				$parsed = $numValue * (1024 * 1024 * 1024); //1073741824
				break;
			case 'm':
				$parsed = $numValue * (1024 * 1024); //1048576
				break;
			case 'k':
				$parsed = $numValue * 1024;

				break;
		}
		if (isset($parsed)) {
			return (int) floor($parsed);
		}

		throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
			'Argument 1 passed to %s is not valid.',
			__METHOD__
		));
	}
}
