<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminatech\DataProvider\DataProvider;
use Illuminatech\DataProvider\Filters\FilterCompare;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request)
    {
        $books = (new DataProvider(SubscriptionPlan::query()))
            ->filters([
                'id',
                'search' => [
                    'name',
                    'description',
                    'price_from' => new FilterCompare('price', '>='),
                    'price_to' => new FilterCompare('price', '<='),
                ],
            ])
            ->sort([
                'id',
                'title',
                'price',
            ])
            ->paginate($request);

        return SubscriptionPlanResource::collection($books);
    }

    public function show(SubscriptionPlan $subscriptionPlan)
    {
        return new SubscriptionPlanResource($subscriptionPlan);
    }
}
