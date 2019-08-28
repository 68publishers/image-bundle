<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone\Content;

use Nette;

final class ProgressBar implements Nette\Utils\IHtmlString
{
	use Nette\SmartObject;

	/** @var array  */
	private $classes = [
		'progress-bar',
	];

	/**
	 * @param array $classes
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\Content\ProgressBar
	 */
	public function setClasses(array $classes): self
	{
		$this->classes = $classes;

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\Content\ProgressBar
	 */
	public function striped(): self
	{
		$this->classes[] = 'progress-bar-striped';

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\Content\ProgressBar
	 */
	public function animated(): self
	{
		$this->classes[] = 'progress-bar-animated';

		return $this;
	}

	/************* interface \Nette\Utils\IHtmlString *************/

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$html = Nette\Utils\Html::el('div class="progress my-2"')
			->addHtml(Nette\Utils\Html::el('div role="progressbar"')->setAttribute('class', $this->classes));

		return (string) $html;
	}
}
