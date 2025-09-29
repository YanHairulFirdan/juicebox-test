<?php

if (! function_exists('empty_object')) {
    function empty_object(int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json(['data' => new stdClass], $status);
    }
}
