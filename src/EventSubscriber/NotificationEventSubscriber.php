<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\EventSubscriber;

use Nette;
use Symfony;
use SixtyEightPublishers;

final class NotificationEventSubscriber implements Symfony\Component\EventDispatcher\EventSubscriberInterface
{
	use Nette\SmartObject;

	/** @var \SixtyEightPublishers\NotificationBundle\INotifierFactory  */
	private $notifierFactory;

	/** @var \Nette\Application\IPresenter|\Nette\Application\UI\Presenter|NULL */
	private $presenter;

	/** @var string  */
	private $notificationEndpoint = SixtyEightPublishers\NotificationBundle\Notification\Notification::ENDPOINT_FLASH_MESSAGE;

	/** @var string|NULL */
	private $prefix;
	
	/** @var \SixtyEightPublishers\NotificationBundle\Notifier|NULL */
	private $notifier;

	/** @var array  */
	private $disabledActions = [
		'success' => [],
		'error' => [],
	];

	/**
	 * @param \SixtyEightPublishers\NotificationBundle\INotifierFactory $notifierFactory
	 * @param \Nette\Application\Application                            $application
	 */
	public function __construct(
		SixtyEightPublishers\NotificationBundle\INotifierFactory $notifierFactory,
		Nette\Application\Application $application
	) {
		$this->notifierFactory = $notifierFactory;
		$this->presenter = $application->getPresenter();

		$application->onPresenter[] = function ($_, Nette\Application\IPresenter $presenter) {
			$this->presenter = $presenter;
		};
	}

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Event\UploadCompletedEvent $event
	 *
	 * @return void
	 */
	public function onUploadCompleted(SixtyEightPublishers\ImageBundle\Event\UploadCompletedEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		$this->getNotifier()
			->success('upload_completed', $event->getFilesCount())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Event\UploadErrorEvent $event
	 *
	 * @return void
	 */
	public function onUploadError(SixtyEightPublishers\ImageBundle\Event\UploadErrorEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		$this->createErrorNotificationBuilder('upload_error', $event->getException())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Event\ActionSuccessEvent $event
	 *
	 * @return void
	 */
	public function onActionSuccess(SixtyEightPublishers\ImageBundle\Event\ActionSuccessEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		if (in_array($event->getActionName(), $this->disabledActions['success'], TRUE)) {
			return;
		}

		$this->getNotifier()
			->success('action_success.' . $event->getActionName())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @internal
	 *
	 * @param \SixtyEightPublishers\ImageBundle\Event\ActionErrorEvent $event
	 *
	 * @return void
	 */
	public function onActionError(SixtyEightPublishers\ImageBundle\Event\ActionErrorEvent $event): void
	{
		if (!$this->isUiPresenter()) {
			return;
		}

		if (in_array($event->getActionName(), $this->disabledActions['error'], TRUE)) {
			return;
		}

		$this->createErrorNotificationBuilder('action_error.' . $event->getActionName(), $event->getException())
			->schedule($this->notificationEndpoint);

		$this->redrawMessages();
	}

	/**
	 * @param string $prefix
	 *
	 * @return \SixtyEightPublishers\ImageBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function setPrefix(string $prefix): self
	{
		$this->prefix = $prefix;
		$this->notifier = NULL;
		
		return $this;
	}

	/**
	 * @param string $notificationEndpoint
	 *
	 * @return \SixtyEightPublishers\ImageBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function setNotificationEndpoint(string $notificationEndpoint): self
	{
		$this->notificationEndpoint = $notificationEndpoint;

		return $this;
	}

	/**
	 * @param array $success
	 * @param array $error
	 *
	 * @return \SixtyEightPublishers\ImageBundle\EventSubscriber\NotificationEventSubscriber
	 */
	public function disableActions(array $success, array $error = []): self
	{
		$this->disabledActions = [
			'success' => $success,
			'error' => $error,
		];

		return $this;
	}

	/**
	 * @return \SixtyEightPublishers\NotificationBundle\Notifier
	 */
	private function getNotifier(): SixtyEightPublishers\NotificationBundle\Notifier
	{
		if (NULL !== $this->notifier) {
			return $this->notifier;
		}
		
		return $this->notifier = $this->notifierFactory->create(
			$this->prefix ?? (str_replace('\\', '_', SixtyEightPublishers\ImageBundle\Control\ImageManager\ImageManagerControl::class) . '.message')
		);
	}

	/**
	 * @return void
	 * @throws \SixtyEightPublishers\ImageBundle\Exception\InvalidStateException
	 */
	private function redrawMessages(): void
	{
		if (NULL === $this->presenter) {
			throw new SixtyEightPublishers\ImageBundle\Exception\InvalidStateException('Current Presenter is not set.');
		}

		if (!$this->isUiPresenter() || !$this->presenter->isAjax()) {
			return;
		}

		# @todo: implement some interface ...
		if (!is_callable([$this->presenter, 'redrawMessages'])) {
			trigger_error(sprintf(
				'Presenter %s does\'t implement method ::redrawMessages(), notifications can\'t be redrawn.',
				get_class($this->presenter)
			));
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$this->presenter->redrawMessages();
	}

	/**
	 * @return bool
	 */
	private function isUiPresenter(): bool
	{
		return $this->presenter instanceof Nette\Application\UI\Presenter;
	}

	/**
	 * @param string                                                 $messageBase
	 * @param \SixtyEightPublishers\ImageBundle\Exception\IException $e
	 *
	 * @return \SixtyEightPublishers\NotificationBundle\Notification\NotificationBuilder
	 */
	private function createErrorNotificationBuilder(string $messageBase, SixtyEightPublishers\ImageBundle\Exception\IException $e): SixtyEightPublishers\NotificationBundle\Notification\NotificationBuilder
	{
		$notifier = $this->getNotifier();

		if ($e instanceof SixtyEightPublishers\ImageBundle\Exception\TranslatableException) {
			return $notifier->error($messageBase . '.' . $e->getMessage(), $e->getArgs());
		}

		return $notifier->error($messageBase . '.default', [
			'code' => $e->getCode(),
			'message' => $e->getMessage(),
		]);
	}
	
	/***************** interface \Symfony\Component\EventDispatcher\EventSubscriberInterface *****************/

	/**
	 * {@inheritdoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			SixtyEightPublishers\ImageBundle\Event\UploadCompletedEvent::NAME => 'onUploadCompleted',
			SixtyEightPublishers\ImageBundle\Event\UploadErrorEvent::NAME => 'onUploadError',
			SixtyEightPublishers\ImageBundle\Event\ActionSuccessEvent::NAME => 'onActionSuccess',
			SixtyEightPublishers\ImageBundle\Event\ActionErrorEvent::NAME => 'onActionError',
		];
	}
}
