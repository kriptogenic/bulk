<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\JobStatus;
use App\Models\Task;
use Illuminate\Contracts\Redis\Factory;
use Illuminate\Redis\Connections\PhpRedisConnection;
use Laravel\Horizon\Contracts\JobRepository;
use Laravel\Horizon\Contracts\TagRepository;
use RuntimeException;

class TaskJobsWaiter
{
    public function __construct(
        private TagRepository $tags,
        private Factory $redis,
    ) {}

    public static function getTag(Task $task): string
    {
        return sprintf('task-waiter:%s', $task->id);
    }

    public function start(Task $task): void
    {
        $this->tags->monitor(self::getTag($task));
    }

    public function wait(Task $task, int $timeout = 60): void
    {
        $startTime = microtime(true);
        $tag = self::getTag($task);
        while (true) {
            $hasCompleted = $this->hasCompleted($tag);
            if ($hasCompleted) {
                return;
            }
            if (microtime(true) - $startTime >= $timeout) {
                throw new RuntimeException('Task timeout.');
            }
            sleep(1);
        }
    }

    private function hasCompleted(string $tag): bool
    {
        $monitored = $this->tags->paginate($tag);

        while (count($monitored) > 0) {
            foreach ($monitored as $job) {
                $status = $this->connection()->hGet($job, 'status');
                if (!is_string($status)) {
                    continue;
                }
                $status = JobStatus::tryFrom($status);
                if (in_array($status, [JobStatus::Pending, JobStatus::Reserved], true)) {
                    return false;
                }
            }
            $this->connection()->zRem($tag, ...$monitored);
            $offset = array_keys($monitored)[count($monitored) - 1] + 1;
            $monitored = $this->tags->paginate($tag, $offset);
        }
        $this->tags->stopMonitoring($tag);
        $this->tags->forget($tag);

        return true;
    }

    private function connection(): PhpRedisConnection
    {
        $connection = $this->redis->connection('horizon');
        if (!$connection instanceof PhpRedisConnection) {
            throw new RuntimeException('For correct working phpredis extension required!');
        }
        return $connection;
    }
}
