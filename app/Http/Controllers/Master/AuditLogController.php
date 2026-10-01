<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of security audit logs.
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = AuditLog::query()->with(['actor', 'targetUser']);

        if ($request->filled('search')) {
            $query->search($request->string('search'));
        }

        if ($request->filled('action')) {
            $query->action($request->string('action'));
        }

        $logs = $query->latest()->paginate(15)->withQueryString();
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $logs,
            ]);
        }

        return view('master.audit-logs.index', compact('logs', 'actions'));
    }
}
