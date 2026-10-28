<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\DineOrder;
use App\Models\Outlet;
use App\Services\DineOrderService;
use App\Services\OutletAccessService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DineOrderController extends Controller
{
    public function __construct(
        private DineOrderService $service,
        private readonly OutletAccessService $outletAccessService,
    ) {}

    public function index(Request $request)
    {
        $outletIds = $request->user()->isSuperAdmin()
            ? null
            : $this->outletAccessService->accessibleOutlets($request->user())->pluck('id')->all();

        $orders = DineOrder::with(['table.area', 'items.product'])
            ->whereIn('status', [DineOrder::STATUS_SUBMITTED, DineOrder::STATUS_ACCEPTED])
            ->when($outletIds !== null, fn ($query) => $query->whereHas(
                'table.area',
                fn ($area) => $area->whereIn('outlet_id', $outletIds)
            ))
            ->latest()
            ->get();

        return Inertia::render('Dashboard/DineIn/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function accept(Request $request, DineOrder $dineOrder)
    {
        $this->ensureOutletAccess($request, $dineOrder);
        $this->service->accept($dineOrder);

        return back()->with('success', 'Pesanan diterima dan siap diproses di kasir.');
    }

    public function reject(Request $request, DineOrder $dineOrder)
    {
        $this->ensureOutletAccess($request, $dineOrder);
        $this->service->reject($dineOrder, $request->input('reason'));

        return back()->with('success', 'Pesanan ditolak.');
    }

    private function ensureOutletAccess(Request $request, DineOrder $dineOrder): void
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return;
        }

        $accessibleOutletIds = $this->outletAccessService->accessibleOutlets($user)->pluck('id');
        $outletId = $dineOrder->table?->area?->outlet_id;

        abort_unless($outletId !== null && $accessibleOutletIds->contains((int) $outletId), 404);
    }
}
