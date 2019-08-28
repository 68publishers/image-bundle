<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Control\DropZone;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class DropZoneControl extends SixtyEightPublishers\SmartNetteComponent\UI\Control implements SixtyEightPublishers\SmartNetteComponent\Translator\ITranslatorAware
{
	use SixtyEightPublishers\SmartNetteComponent\Translator\TTranslatorAware;

	/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\Symfony\Component\EventDispatcher\EventDispatcher  */
	private $eventDispatcher;

	/** @var array  */
	private $settings = [];

	/** @var string[]  */
	private $acceptedMimeTypes = [];

	/** @var string[]  */
	private $acceptedExtensions = [];

	/** @var NULL|string */
	private $dropZoneId;

	/** @var \Nette\Utils\IHtmlString[] */
	private $contentHtml = [];

	/** @var array */
	private $extensions = [];

	/**
	 * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
	 */
	public function __construct(Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher)
	{
		parent::__construct();

		$this->eventDispatcher = $eventDispatcher;
	}

	/**
	 * @internal
	 *
	 * @param int|NULL $filesCount
	 *
	 * @return void
	 */
	public function handleCompleted(?int $filesCount = NULL): void
	{
		$this->eventDispatcher->dispatch(new SixtyEightPublishers\ImageBundle\Event\UploadCompletedEvent($filesCount));
	}

	/**
	 * @internal
	 *
	 * @throws \Nette\Utils\JsonException
	 * @return void
	 */
	public function render(): void
	{
		$this->template->setTranslator($this->getPrefixedTranslator());

		$this->template->settings = Nette\Utils\Json::encode($this->settings);
		$this->template->extensions = Nette\Utils\Json::encode($this->extensions);
		$this->template->contentHtml = $this->contentHtml;

		$this->doRender();
	}

	/**
	 * @param \Nette\Utils\IHtmlString $htmlString
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function addContentHtml(Nette\Utils\IHtmlString $htmlString): self
	{
		if ($htmlString instanceof Content\ITranslatableHtmlString) {
			$htmlString->setTranslator($this->getPrefixedTranslator());
		}

		if ($htmlString instanceof Content\ProgressBar) {
			$this->addExtension('progressbar');
		}

		$this->contentHtml[] = $htmlString;

		return $this;
	}

	/**
	 * @param array $settings
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function setSettings(array $settings): self
	{
		foreach ($settings as $key => $setting) {
			$this->addSetting($key, $setting);
		}

		return $this;
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function addSetting(string $key, $value): self
	{
		if ($key === 'acceptedFiles') {
			$this->setAccepted(is_array($value) ? $value : explode(',', (string) $value));
		} else {
			$this->settings[$key] = $value;
		}

		return $this;
	}

	/**
	 * @param array $accepted
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function setAccepted(array $accepted): self
	{
		$this->acceptedExtensions = $this->acceptedMimeTypes = [];

		foreach ($accepted as $accept) {
			if (preg_match('~^[-\w\+\*]+/[-\w\+\*]+$~', $accept)) {
				$this->acceptedMimeTypes[] = $accept;
			} elseif (preg_match('~^\.[\w\.]+$~', $accept)) {
				$this->acceptedExtensions[] = $accept;
			} else {
				throw new SixtyEightPublishers\ImageBundle\Exception\InvalidArgumentException(sprintf(
					'Accepted file type "%s" is not valid mime-type or file extension.',
					$accept
				));
			}
		}

		$this->settings['acceptedFiles'] = implode(',', $accepted);

		return $this;
	}

	/**
	 * @param string $dropZoneId
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function setDropZoneId(string $dropZoneId): self
	{
		$this->dropZoneId = $dropZoneId;

		return $this;
	}

	/**
	 * @param string $name
	 * @param array  $options
	 *
	 * @return \SixtyEightPublishers\ImageBundle\Control\DropZone\DropZoneControl
	 */
	public function addExtension(string $name, array $options = []): self
	{
		$this->extensions[$name] = $options;

		return $this;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentUploadForm(): Nette\Application\UI\Form
	{
		$form = new Nette\Application\UI\Form();

		$form->elementPrototype->setAttribute('class', 'dropzone');

		if (is_string($this->dropZoneId)) {
			$form->elementPrototype->setAttribute('id', $this->dropZoneId);
		}

		$form->addUpload('file');

		$form->addProtection();

		$form->onSuccess[] = [$this, 'doUpload'];

		return $form;
	}

	/**
	 * @internal
	 * @param \Nette\Application\UI\Form $form
	 *
	 * @return void
	 */
	public function doUpload(Nette\Application\UI\Form $form): void
	{
		try {
			$file = $form->values->file;

			if (!$file instanceof Nette\Http\FileUpload || !$file->isOk()) {
				throw SixtyEightPublishers\ImageBundle\Exception\UploadException::invalidFileUpload();
			}

			if (!$this->isUploadAcceptable($file)) {
				throw SixtyEightPublishers\ImageBundle\Exception\UploadException::unsupportedType($file->getContentType());
			}

			$this->eventDispatcher->dispatch(
				new SixtyEightPublishers\ImageBundle\Event\FileUploadEvent($file),
				SixtyEightPublishers\ImageBundle\Event\FileUploadEvent::NAME
			);
		} catch (SixtyEightPublishers\ImageBundle\Exception\IException $e) {
			$this->eventDispatcher->dispatch(
				new SixtyEightPublishers\ImageBundle\Event\UploadErrorEvent($e),
				SixtyEightPublishers\ImageBundle\Event\UploadErrorEvent::NAME
			);
		}
	}

	/**
	 * @return string
	 */
	protected function createPrefixedTranslatorDomain(): string
	{
		return str_replace('\\', '_', static::class);
	}

	/**
	 * @param \Nette\Http\FileUpload $upload
	 *
	 * @return bool
	 */
	private function isUploadAcceptable(Nette\Http\FileUpload $upload): bool
	{
		if (empty($this->acceptedMimeTypes) && empty($this->acceptedExtensions)) {
			return TRUE;
		}

		if (is_string($mime = $upload->getContentType())) {
			[ $mimeStart, $mimeEnd ] = explode('/', $mime);
			$acceptedForms = [ $mime, '*/' . $mimeStart, $mimeEnd . '/*' ];

			foreach ($this->acceptedMimeTypes as $acceptedMimeType) {
				if (in_array($acceptedMimeType, $acceptedForms, TRUE)) {
					return TRUE;
				}
			}
		}

		$name = $upload->getName();

		foreach ($this->acceptedExtensions as $acceptedExtension) {
			if (Nette\Utils\Strings::endsWith($name, $acceptedExtension)) {
				return TRUE;
			}
		}

		return FALSE;
	}
}
