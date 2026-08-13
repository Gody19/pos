<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct(string $productName = '', int $available = 0)
    {
        parent::__construct(
            $productName !== ''
                ? "Out of stock: \"{$productName}\" only {$available} unit(s) available."
                : 'Product is out of stock.',
            422
        );
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['stock' => [$this->getMessage()]],
        ], 422);
    }
}
