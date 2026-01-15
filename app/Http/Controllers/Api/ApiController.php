<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Cursor;

class ApiController extends Controller
{
    protected const DEFAULT_PER_PAGE = 20;

    protected function valiateCursor(Request $request, string $cursorName): Request
    {
        $cursor = $request->query($cursorName);
        if ($cursor) {
            try {
                Cursor::fromEncoded($cursor);
            } catch (\Throwable) {
                $request->query->remove($cursorName);
            }
        }
        return $request;
    }
}
