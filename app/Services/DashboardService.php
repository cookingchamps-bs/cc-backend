<?php

namespace App\Services;

use App\Models\{Kid, Order, Recipe, Story, User, UserSubscription};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardData(): array
    {
        return [
            'overview' => $this->getOverviewCounts(),
            'shop_revenue' => $this->getShopRevenueWindows(),
            'subscription_revenue' => $this->getSubscriptionRevenueWindows(),
            'latest_stories' => $this->getLatestStories(10),
            'recent_orders' => $this->getRecentPaidOrders(12),
            'recent_subscriptions' => $this->getRecentSubscriptions(12),
        ];
    }

    public function getOverviewCounts(): array
    {
        return [
            'parent_count' => User::count(),
            'kid_count' => Kid::count(),
            'story_count' => Story::count(),
            'recipe_count' => Recipe::count(),
            'active_subscriptions' => UserSubscription::query()
                ->where('payment_status', true)
                ->where('is_active', true)
                ->count(),
            'pending_orders' => Order::query()->where('status', 0)->count(),
            'paid_orders_all_time' => Order::query()->where('status', 1)->count(),
        ];
    }

    public function getShopRevenueWindows(): array
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        return [
            'today' => $this->paidOrderAggregate($now->copy()->startOfDay(), $now->copy()->endOfDay()),
            'week' => $this->paidOrderAggregate($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'month' => $this->paidOrderAggregate($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
        ];
    }

    public function getSubscriptionRevenueWindows(): array
    {
        $now = Carbon::now(config('app.timezone', 'UTC'));

        return [
            'today' => $this->subscriptionRevenueAggregate($now->copy()->startOfDay(), $now->copy()->endOfDay()),
            'week' => $this->subscriptionRevenueAggregate($now->copy()->startOfWeek(), $now->copy()->endOfWeek()),
            'month' => $this->subscriptionRevenueAggregate($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
        ];
    }

    /**
     * Paid product orders only (status = 1). Amounts exclude tax in display note — total_amount is order total.
     */
    protected function paidOrderAggregate(Carbon $start, Carbon $end): array
    {
        $row = Order::query()
            ->where('status', 1)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as gross, COALESCE(SUM(tax_amount), 0) as tax, COUNT(*) as cnt')
            ->first();

        return [
            'gross' => (float) ($row->gross ?? 0),
            'tax' => (float) ($row->tax ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }

    /**
     * Subscription purchases: sum plan amount from subscriptions table for successful payments in window.
     */
    protected function subscriptionRevenueAggregate(Carbon $start, Carbon $end): array
    {
        $row = DB::table('user_subscriptions')
            ->join('subscriptions', 'user_subscriptions.subscription_id', '=', 'subscriptions.id')
            ->whereNull('user_subscriptions.deleted_at')
            ->whereNull('subscriptions.deleted_at')
            ->where('user_subscriptions.payment_status', 1)
            ->whereBetween('user_subscriptions.created_at', [$start, $end])
            ->selectRaw('COALESCE(SUM(subscriptions.amount), 0) as total, COUNT(*) as cnt')
            ->first();

        return [
            'total' => (float) ($row->total ?? 0),
            'count' => (int) ($row->cnt ?? 0),
        ];
    }

    public function getLatestStories(int $limit)
    {
        return Story::query()
            ->with('storyAddedByParent')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentPaidOrders(int $limit)
    {
        return Order::query()
            ->with('User')
            ->where('status', 1)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentSubscriptions(int $limit)
    {
        return UserSubscription::query()
            ->with(['user', 'kid', 'subscriptionPlan'])
            ->where('payment_status', true)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
