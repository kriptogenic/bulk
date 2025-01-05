<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ChatAction;
use App\Enums\SendMethod;
use App\Services\Sender;
use Illuminate\Console\Command;

class WorkerCommand extends Command
{
    protected $signature = 'worker';

    protected $description = 'Command description';

    public function handle(Sender $sender): void
    {
        $results = $sender->send(
            env('TEST_TOKEN'),
            SendMethod::SendChatAction,
            collect([
                5013564874,
            ]), [
                'action' => ChatAction::Typing->value,
            ],
        );
        foreach ($results as $result) {
            dump($result);
        }
    }
}
