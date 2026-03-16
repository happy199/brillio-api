<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use App\Models\ScheduledTaskLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function emails(Request $request)
    {
        $query = EmailLog::query();

        if ($request->filled('search')) {
            $query->where('to', 'like', '%'.$request->search.'%')
                ->orWhere('subject', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest('sent_at')->paginate(20);

        return view('admin.audits.emails', compact('logs'));
    }

    public function crons(Request $request)
    {
        $query = ScheduledTaskLog::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('command', 'like', '%'.$request->search.'%');
        }

        $logs = $query->latest('run_at')->paginate(20);

        return view('admin.audits.crons', compact('logs'));
    }
}
