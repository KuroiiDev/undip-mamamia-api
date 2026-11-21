<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $storeCount = Restaurant::count();
        $userCount = User::whereIn('role', ['user', 'merchant'])->count();
        $pendingCount = Restaurant::where('status', 'pending')->count();

        // If reports table exists in optional phase 9, count open reports
        $openReportCount = 0;
        if (Schema::hasTable('reports')) {
            $openReportCount = \Illuminate\Support\Facades\DB::table('reports')
                ->where('status', 'pending')
                ->count();
        }

        $pendingStores = Restaurant::with('owner:id,name,email')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Restaurant $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'owner_name' => $r->owner?->name ?? '-',
                'address' => $r->address,
                'created_at' => $r->created_at?->toISOString(),
                'cover_url' => ApiResponse::storageUrl($r->cover_path),
            ]);

        return ApiResponse::success([
            'store_count' => $storeCount,
            'user_count' => $userCount,
            'pending_count' => $pendingCount,
            'open_report_count' => $openReportCount,
            'pending_stores' => $pendingStores,
        ]);
    }
}
