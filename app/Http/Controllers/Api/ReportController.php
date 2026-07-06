<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->botUser;

        $reports = Report::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reports);
    }

    public function download(Request $request, Report $report)
    {
        $user = $request->botUser;

        if ($report->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        if (!Storage::exists($report->file_path)) {
            abort(404, 'Файл не найден');
        }

        return Storage::download($report->file_path, $report->file_name);
    }

    public function destroy(Request $request, Report $report)
    {
        $user = $request->botUser;

        if ($report->user_id !== $user->id) {
            abort(403, 'Доступ запрещен');
        }

        $report->delete();

        return response()->noContent();
    }
}
