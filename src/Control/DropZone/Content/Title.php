<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone\Content;

use Nette;

final class Title implements ITranslatableHtmlString
{
	use Nette\SmartObject,
		TTranslatableHtmlString;

	/** @var string  */
	private $title;

	/** @var bool  */
	private $translate;

	/**
	 * @param string $title
	 * @param bool   $translate
	 */
	public function __construct(string $title = 'default_title', bool $translate = TRUE)
	{
		$this->title = $title;
		$this->translate = $translate;
	}

	/************* interface \SixtyEightPublishers\ImageBundle\Control\DropZone\Content\ITranslatableHtmlString *************/

	/**
	 * {@inheritdoc}
	 */
	public function __toString(): string
	{
		$html = Nette\Utils\Html::el('span')
			->setText($this->translate ? $this->getTranslator()->translate($this->title) : $this->title);

		return (string) $html;
	}
}
