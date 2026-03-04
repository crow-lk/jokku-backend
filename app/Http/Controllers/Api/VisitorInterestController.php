<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitorInterest\StoreVisitorInterestRequest;
use App\Models\VisitorInterest;
use Illuminate\Http\JsonResponse;

class VisitorInterestController extends Controller
{
    public function store(StoreVisitorInterestRequest $request): JsonResponse
    {
        $visitorInterest = VisitorInterest::query()->create([
            ...$request->validated(),
            'status' => VisitorInterest::STATUS_NEW,
            'source' => VisitorInterest::SOURCE_WEB,
        ]);

        return response()->json([
            'message' => 'Thanks for your interest. We will get back to you soon.',
            'data' => $visitorInterest,
        ], 201);
    }
}
