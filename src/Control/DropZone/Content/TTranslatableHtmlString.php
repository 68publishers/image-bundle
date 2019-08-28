<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone\Content;

use Nette;
use SixtyEightPublishers;

trait TTranslatableHtmlString
{
	/** @var \Nette\Localization\ITranslator|NULL */
	private $translator;

	/**
	 * @param \Nette\Localization\ITranslator $translator
	 *
	 * @return void
	 */
	public function setTranslator(Nette\Localization\ITranslator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 */
	protected function getTranslator(): Nette\Localization\ITranslator
	{
		if (NULL === $this->translator) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException(sprintf(
				'Translator for class %s is not set.',
				static::class
			));
		}

		return $this->translator;
	}
}
