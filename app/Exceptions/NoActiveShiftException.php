<?php

namespace App\Exceptions;

use Exception;

class NoActiveShiftException extends Exception
{
    public function __construct(string $message = 'No active shift. Please open a shift before making a sale.')
    {
        parent::__construct($message, 409);
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['shift' => [$this->getMessage()]],
        ], 409);
    }
}
