<?php

declare(strict_types=1);

namespace SixtyEightPublishers\ImageBundle\Exception;

final class TranslatableException extends \Exception implements IException
{
	/** @var array  */
	private $args;

	/**
	 * @param string          $message
	 * @param array|NULL      $args
	 * @param int             $code
	 * @param \Throwable|NULL $previous
	 */
	public function __construct(string $message, ?array $args = NULL, int $code = 0, \Throwable $previous = NULL)
	{
		parent::__construct($message, $code, $previous);

		$this->args = array_merge([
			'code' => $code,
			'original_message' => $previous ? $previous->getMessage() : '',
		], $args ?? []);
	}

	/**
	 * @return array
	 */
	public function getArgs(): array
	{
		return $this->args;
	}
}
