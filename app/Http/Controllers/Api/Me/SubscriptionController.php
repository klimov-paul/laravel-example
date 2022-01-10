<?php

namespace App\Http\Controllers\Api\Me;

use App\Exceptions\PaymentException;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Subscription\SubscriptionCheckout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = QueryBuilder::for(
            $this->user()
                ->subscriptions()
                ->with('subscriptionPlan')
        )
            ->allowedSorts(['id', 'subscription_plan_id', 'created_at'])
            ->defaultSort('-id')
            ->paginate();

        return SubscriptionResource::collection($subscriptions);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'subscription_plan_id' => ['required', 'integer', 'exists:subscription_plans,id'],
            'token' => ['sometimes', 'required', 'string'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        $subscriptionPlan = SubscriptionPlan::query()
            ->findOrFail($validatedData['subscription_plan_id']);

        try {
            $checkout = new SubscriptionCheckout($request->user(), $subscriptionPlan);
            $subscription = $checkout->process($validatedData['token'] ?? null);
        } catch (PaymentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return [
            'message' => __('Subscription has been created successfully.'),
            'data' => [
                'subscription' => new SubscriptionResource($subscription),
            ],
        ];
    }

    public function show(Subscription $subscription)
    {
        if ($subscription->user_id !== $this->user()->id) {
            abort(404);
        }

        return new SubscriptionResource($subscription);
    }

    protected function user(): User
    {
        return Auth::guard()->user();
    }
}
