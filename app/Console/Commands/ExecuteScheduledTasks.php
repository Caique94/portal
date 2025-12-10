<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledTask;
use App\Models\FechamentoJob;
use App\Jobs\ProcessFechamentoJob;
use Illuminate\Support\Str;

class ExecuteScheduledTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fechamento:execute-scheduled
                            {--task-id= : Execute specific task by ID}
                            {--dry-run : Show what would be executed without running}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute scheduled fechamento tasks that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for due scheduled tasks...');

        $query = ScheduledTask::with('creator')->enabled();

        if ($this->option('task-id')) {
            $query->where('id', $this->option('task-id'));
        } else {
            $query->due();
        }

        $tasks = $query->get();

        if ($tasks->isEmpty()) {
            $this->info('No tasks are due for execution.');
            return 0;
        }

        $this->info("Found {$tasks->count()} task(s) to execute.");

        foreach ($tasks as $task) {
            $this->processTask($task);
        }

        $this->info('All due tasks have been processed.');
        return 0;
    }

    private function processTask(ScheduledTask $task)
    {
        $this->line("Processing task #{$task->id}: {$task->type} fechamento");

        if ($this->option('dry-run')) {
            $this->warn('[DRY RUN] Would create job for task: ' . json_encode([
                'type' => $task->type,
                'filters' => $task->filters,
                'cron' => $task->cron_expr,
                'next_run' => $task->next_run,
            ]));
            return;
        }

        try {
            $periodEnd = now();
            $periodStart = $this->calculatePeriodStart($task->cron_expr, $periodEnd);

            $job = FechamentoJob::create([
                'id' => (string) Str::uuid(),
                'type' => $task->type,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'filters' => $task->filters,
                'requested_by' => $task->created_by,
                'status' => 'queued',
            ]);

            ProcessFechamentoJob::dispatch($job->id);

            $task->markAsRun();

            $this->info("✓ Created job {$job->id} for task #{$task->id}");
            $this->line("  Period: {$periodStart->format('d/m/Y')} - {$periodEnd->format('d/m/Y')}");
            $this->line("  Next run: {$task->next_run->format('d/m/Y H:i')}");

        } catch (\Exception $e) {
            $this->error("✗ Error processing task #{$task->id}: {$e->getMessage()}");
            \Log::error("Error executing scheduled task {$task->id}: {$e->getMessage()}", [
                'exception' => $e,
                'task' => $task->toArray(),
            ]);
        }
    }

    private function calculatePeriodStart($cronExpr, $periodEnd)
    {
        if (str_contains($cronExpr, '0 0 1 * *')) {
            return $periodEnd->copy()->subMonth()->startOfMonth();
        }

        if (str_contains($cronExpr, '0 0 * * 1')) {
            return $periodEnd->copy()->subWeek()->startOfWeek();
        }

        if (str_contains($cronExpr, '0 0 * * *')) {
            return $periodEnd->copy()->subDay()->startOfDay();
        }

        return $periodEnd->copy()->subMonth();
    }
}
