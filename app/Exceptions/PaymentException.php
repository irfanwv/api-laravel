<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentException extends HttpException
{
	private $data;

	public function __construct ($message, $data)
	{
		$this->data = $data;
		parent::__construct(402, $message, null, [], 402);
	}

	public function getData ()
	{
		return $this->data;
	}
}
