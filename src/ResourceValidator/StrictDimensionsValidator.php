<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\ResourceValidator;

use SixtyEightPublishers;

final class StrictDimensionsValidator implements IResourceValidator
{
	/** @var string[]  */
	private $allowedDimensions;

	/**
	 * Use these formats:
	 *      - "600x300" => width 600 and height 300
	 *      - "600x" => width 600, arbitrary height
	 *      - "x600" => arbitrary width, height 600
	 *
	 * @param array $allowedDimensions
	 */
	public function __construct(array $allowedDimensions)
	{
		$this->allowedDimensions = $allowedDimensions;
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

		$dimensions = [
			$width . 'x',
			'x' . $height,
			$width . 'x' . $height,
		];

		if (0 < count(array_intersect($dimensions, $this->allowedDimensions))) {
			return;
		}

		$args = [
			'width' => $width,
			'height' => $height,
			'allowed' => implode(', ', $this->allowedDimensions),
		];

		throw new SixtyEightPublishers\ImageBundle\Exception\TranslatableException(
			'strict_dimensions_validator',
			$args,
			0,
			SixtyEightPublishers\ImageBundle\Exception\ImageManipulationException::error('validation - strict dimensions')
		);
	}
}
