<?php

namespace App\Exceptions;

use Exception;

class PaymentMismatchException extends Exception
{
    public function __construct(string $message = 'The sum of payments does not match the sale total.')
    {
        parent::__construct($message, 422);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['payments' => [$this->getMessage()]],
        ], 422);
    }
}
