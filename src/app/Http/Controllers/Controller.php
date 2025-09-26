<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function tryCatch(callable $callback)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
