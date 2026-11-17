<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display a listing of system audit logs (Admin only).
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with('user')->latest();

        // Optional filter by action or entity_type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }

        $auditLogs = $query->paginate(20)->withQueryString();
        $totalLogs = AuditLog::count();

        return view('audit-logs.index', compact('auditLogs', 'totalLogs'));
    }
}
