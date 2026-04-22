<?php

namespace App\Http\Controllers;

use App\Support\Plans;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class BillingController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $current = Plans::resolveForUser($user);
        $sub = $user->subscription('default');

        return Inertia::render('Billing/Index', [
            'plans' => Plans::all(),
            'current_plan' => $current,
            'configured' => filled(config('cashier.secret')),
            'subscription' => $sub ? [
                'name' => $sub->name,
                'stripe_status' => $sub->stripe_status,
                'ends_at' => $sub->ends_at,
                'trial_ends_at' => $sub->trial_ends_at,
                'on_grace_period' => $sub->onGracePeriod(),
                'on_trial' => $sub->onTrial(),
                'canceled' => $sub->canceled(),
            ] : null,
        ]);
    }

    public function checkout(Request $request, string $plan): SymfonyRedirectResponse|RedirectResponse
    {
        $planData = Plans::get($plan);
        if (! $planData || ! $planData['stripe_price']) {
            return back()->with('error', 'This plan is not available for self-serve checkout.');
        }
        if (! filled(config('cashier.secret'))) {
            return back()->with('error', 'Stripe is not configured on this server.');
        }

        return $request->user()
            ->newSubscription('default', $planData['stripe_price'])
            ->trialDays(14)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('billing.index') . '?success=1',
                'cancel_url' => route('billing.index') . '?canceled=1',
                'locale' => $request->user()->locale ?? 'auto',
                'tax_id_collection' => ['enabled' => true],
                'automatic_tax' => ['enabled' => true],
                'billing_address_collection' => 'required',
            ]);
    }

    public function portal(Request $request)
    {
        if (! $request->user()->hasStripeId()) {
            return back()->with('error', 'No billing account yet. Start a subscription first.');
        }
        return $request->user()->redirectToBillingPortal(route('billing.index'));
    }

    public function cancel(Request $request): RedirectResponse
    {
        $sub = $request->user()->subscription('default');
        if ($sub && ! $sub->canceled()) {
            $sub->cancel();
        }
        return back()->with('success', __('Subscription will end at the period end.'));
    }

    public function resume(Request $request): RedirectResponse
    {
        $sub = $request->user()->subscription('default');
        if ($sub && $sub->onGracePeriod()) {
            $sub->resume();
        }
        return back()->with('success', __('Subscription resumed.'));
    }
}
