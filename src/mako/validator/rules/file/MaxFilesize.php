<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\WithParametersTrait;
use mako\validator\rules\WithParametersInterface;

/**
 * Max filesize rule.
 *
 * @author Frederic G. Østby
 */
class MaxFilesize extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['maxSize'];

	/**
	 * Convert human friendly size to bytes.
	 *
	 * @param  int|string $size Size
	 * @return int
	 */
	protected function convertToBytes($size): int
	{
		switch(substr($size, -3))
		{
			case 'KiB':
				return substr($size, 0, -3) * 1024;
			case 'MiB':
				return substr($size, 0, -3) * (1024 ** 2);
			case 'GiB':
				return substr($size, 0, -3) * (1024 ** 3);
			case 'TiB':
				return substr($size, 0, -3) * (1024 ** 4);
			default:
				return (int) $size;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$maxSize = $this->convertToBytes($this->getParameter('maxSize'));

		var_dump($maxSize);

		return $value->getSize() <= $maxSize;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be less than %2$s in size.', $field, $this->parameters['maxSize']);
	}
}
