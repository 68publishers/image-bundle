<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone\Content;

use Nette;

interface ITranslatableHtmlString extends Nette\Utils\IHtmlString
{
	/**
	 * @return string
	 */
	public function __toString(): string;

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 *
	 * @return void
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator): void;
}
