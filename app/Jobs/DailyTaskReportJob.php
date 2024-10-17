<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class DailyTaskReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Query tasks for the daily report
        $tasks = Task::whereDate('due_date', now()->toDateString())
            ->orWhere('status', 'Completed')
            ->get();

        // Logic to generate a report, maybe save to file or email
        // You can also log or save this report in the database
        // For demonstration, we'll log the report
        Log::info('Daily Task Report', ['tasks' => $tasks]);
    }
}
