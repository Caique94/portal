<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FechamentoJob;
use App\Models\FechamentoAudit;
use App\Jobs\ProcessFechamentoJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FechamentoJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = FechamentoJob::with('requestedBy');

        if ($request->has('type')) {
            $query->byType($request->type);
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        if ($request->has('user_id')) {
            $query->where('requested_by', $request->user_id);
        }

        $perPage = $request->input('per_page', 15);
        $jobs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($jobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:client,consultant',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'filters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $existingJob = FechamentoJob::where('type', $request->type)
            ->where('period_start', $request->period_start)
            ->where('period_end', $request->period_end)
            ->where('filters', json_encode($request->filters ?? []))
            ->where('requested_by', auth()->id())
            ->whereIn('status', ['queued', 'processing'])
            ->first();

        if ($existingJob) {
            return response()->json([
                'success' => false,
                'message' => 'Um job idêntico já está em processamento',
                'job_id' => $existingJob->id,
            ], 409);
        }

        $job = FechamentoJob::create([
            'id' => (string) Str::uuid(),
            'type' => $request->type,
            'period_start' => $request->period_start,
            'period_end' => $request->period_end,
            'filters' => $request->filters,
            'requested_by' => auth()->id(),
            'status' => 'queued',
        ]);

        FechamentoAudit::log('gerar', $job->id, auth()->id(), [
            'type' => $request->type,
            'period' => "{$request->period_start} - {$request->period_end}",
        ]);

        ProcessFechamentoJob::dispatch($job->id);

        return response()->json([
            'success' => true,
            'message' => 'Job criado com sucesso',
            'job' => $job,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $job = FechamentoJob::with(['requestedBy', 'history', 'audits.user'])->find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job não encontrado',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'job' => $job,
        ]);
    }

    /**
     * Reprocess a job
     */
    public function reprocess(string $id)
    {
        $job = FechamentoJob::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job não encontrado',
            ], 404);
        }

        if ($job->isProcessing()) {
            return response()->json([
                'success' => false,
                'message' => 'Job já está em processamento',
            ], 409);
        }

        $job->update([
            'status' => 'queued',
            'error_message' => null,
            'version' => $job->version + 1,
        ]);

        FechamentoAudit::log('reprocessar', $job->id, auth()->id(), [
            'old_version' => $job->version - 1,
            'new_version' => $job->version,
        ]);

        ProcessFechamentoJob::dispatch($job->id);

        return response()->json([
            'success' => true,
            'message' => 'Job reenfileirado para reprocessamento',
            'job' => $job,
        ]);
    }

    /**
     * Download the PDF
     */
    public function download(string $id)
    {
        $job = FechamentoJob::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job não encontrado',
            ], 404);
        }

        if (!$job->pdf_url) {
            return response()->json([
                'success' => false,
                'message' => 'PDF ainda não foi gerado',
            ], 404);
        }

        if (!Storage::disk('public')->exists($job->pdf_url)) {
            return response()->json([
                'success' => false,
                'message' => 'Arquivo PDF não encontrado',
            ], 404);
        }

        FechamentoAudit::log('baixar', $job->id, auth()->id(), [
            'filename' => basename($job->pdf_url),
        ]);

        return Storage::disk('public')->download($job->pdf_url);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $job = FechamentoJob::find($id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job não encontrado',
            ], 404);
        }

        if ($job->pdf_url && Storage::disk('public')->exists($job->pdf_url)) {
            Storage::disk('public')->delete($job->pdf_url);
        }

        FechamentoAudit::log('deletar', $job->id, auth()->id());

        $job->delete();

        return response()->json([
            'success' => true,
            'message' => 'Job deletado com sucesso',
        ]);
    }
}
