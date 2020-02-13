<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceValidator;

use SixtyEightPublishers;

final class RelativeDimensionsValidator implements IResourceValidator
{
	public const    MODE_LTE = 'lte',
					MODE_GTE = 'gte';

	/** @var int|NULL  */
	private $width;

	/** @var int|NULL  */
	private $height;

	/** @var string  */
	private $mode;

	/** @var string  */
	private $mask;

	/**
	 * @param int|NULL $width
	 * @param int|NULL $height
	 * @param string   $mode
	 *
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException
	 */
	public function __construct(?int $width, ?int $height, string $mode = self::MODE_GTE)
	{
		if (NULL === $width && NULL === $height) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException('Width an Height arguments are NULL but one of these arguments must be specified at least.');
		}

		if (!in_array($mode, [ self::MODE_LTE, self::MODE_GTE ], TRUE)) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
				'Mode "%s" is not supported.',
				$mode
			));
		}

		$this->width = $width;
		$this->height = $height;
		$this->mode = $mode;
		$this->mask = sprintf(
			'%d%d',
			NULL !== $this->width,
			NULL !== $this->height
		);
	}

	/**
	 * @param int $width
	 * @param int $height
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\TranslatableException
	 */
	private function validateGte(int $width, int $height): void
	{
		if ((NULL === $this->width ? TRUE : $width >= $this->width) && (NULL === $this->height ? TRUE : $height >= $this->height)) {
			return;
		}

		switch ($this->mask) {
			case '11':
				throw $this->createException('relative_dimensions_validator.gte_both', $width, $height);
			case '10':
				throw $this->createException('relative_dimensions_validator.gte_width', $width, $height);
			case '01':
				throw $this->createException('relative_dimensions_validator.gte_height', $width, $height);
		}
	}

	/**
	 * @param int $width
	 * @param int $height
	 *
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\TranslatableException
	 */
	private function validateLte(int $width, int $height): void
	{
		if ((NULL === $this->width ? TRUE : $width <= $this->width) && (NULL === $this->height ? TRUE : $height <= $this->height)) {
			return;
		}

		switch ($this->mask) {
			case '11':
				throw $this->createException('relative_dimensions_validator.lte_both', $width, $height);
			case '10':
				throw $this->createException('relative_dimensions_validator.lte_width', $width, $height);
			case '01':
				throw $this->createException('relative_dimensions_validator.lte_height', $width, $height);
		}
	}

	/**
	 * @param string $message
	 * @param int    $width
	 * @param int    $height
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Exception\TranslatableException
	 */
	private function createException(string $message, int $width, int $height): SixtyEightPublishers\ImageBundle\Exception\TranslatableException
	{
		$args = [
			'width' => $width,
			'height' => $height,
		];

		if (NULL !== $this->width) {
			$args['required_width'] = $this->width;
		}

		if (NULL !== $this->height) {
			$args['required_height'] = $this->height;
		}

		$previous = SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('validator - relative dimensions');

		return new SixtyEightPublishers\ImageBundle\Exception\TranslatableException($message, $args, 0, $previous);
	}

	/************* interface \SixtyEightPublishers\ImageBundle\ResourceValidator\IResourceValidator *************/

	/**
	 * {@inheritDoc}
	 */
	public function validate(SixtyEightPublishers\ImageStorage\Resource\IResource $resource): void
	{
		$image = $resource->getImage();

		$width = $image->width();
		$height = $image->height();

		switch ($this->mode) {
			case self::MODE_GTE:
				$this->validateGte($width, $height);

				break;
			case self::MODE_LTE:
				$this->validateLte($width, $height);

				break;
		}
	}
}
