<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Throwable;

class ProcessBulkBuildingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $jobId;

    // allow long processing
    public $timeout = 1200; // 20 minutes
    public $tries = 1;      // start simple; increase later if needed

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function handle()
    {
        $payloadKey = "bulkbuilding:payload:{$this->jobId}";
        $statusKey  = "bulkbuilding:status:{$this->jobId}";
        $resultKey  = "bulkbuilding:result:{$this->jobId}";

        Cache::put($statusKey, 'running', now()->addHours(6));

        $data = Cache::get($payloadKey);
        if (!$data) {
            Cache::put($statusKey, 'failed', now()->addHours(6));
            Cache::put($resultKey, [
                'inserted' => 0,
                'failed' => 0,
                'errors' => [['error' => 'Payload missing from cache (expired or never stored).']]
            ], now()->addHours(6));
            return;
        }

        $transaction_type = $data['transaction_type'] ?? null;
        $records = $data['records'] ?? [];

        $summary = ['inserted' => 0, 'failed' => 0, 'errors' => []];

        try {
            foreach (array_chunk($records, 20) as $chunkIndex => $chunk) {
                DB::beginTransaction();

                try {
                    foreach ($chunk as $i => $row) {
                        $rowIndex = $chunkIndex * 20 + $i;

                        try {
                            $rowRequest = Request::create('/', 'POST', $row);

                            $res = app(\App\Http\Controllers\Api\EbpsBuildingController::class)
                                ->storeBuildingInfo($transaction_type, $rowRequest);

                            
                            if ($res instanceof JsonResponse) {
                                $payload = $res->getData(true);
                                if (($payload['success'] ?? true) === false) {
                                    throw new \Exception($payload['message'] ?? ($payload['error_details'] ?? 'Row failed'));
                                }
                            }

                            $summary['inserted']++;
                        } catch (Throwable $e) {
                            $summary['failed']++;
                            $summary['errors'][] = [
                                'index' => $rowIndex,
                                'ApplicationNumber' => $row['ApplicationNumber'] ?? null,
                                'BldgPrmt_TID' => $row['BldgPrmt_TID'] ?? null,
                                'error' => $e->getMessage(),
                            ];
                        }
                    }

                    DB::commit();
                } catch (Throwable $e) {
                    DB::rollBack();
                    $summary['errors'][] = [
                        'chunk' => $chunkIndex,
                        'error' => 'Chunk rolled back: ' . $e->getMessage()
                    ];
                }
            }

            Cache::put($resultKey, $summary, now()->addHours(6));
            Cache::put($statusKey, 'done', now()->addHours(6));
        } catch (Throwable $e) {
            // ✅ any fatal crash gets reported
            $summary['errors'][] = ['error' => 'Job crashed: ' . $e->getMessage()];
            Cache::put($resultKey, $summary, now()->addHours(6));
            Cache::put($statusKey, 'failed', now()->addHours(6));
        }
    }
}
