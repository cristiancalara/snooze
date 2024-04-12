<?php

namespace Thomasjohnkane\Snooze\Console\Commands;

use Illuminate\Console\Command;
use Thomasjohnkane\Snooze\Models\ScheduledNotification as ScheduledNotificationModel;
use Thomasjohnkane\Snooze\ScheduledNotification;

class SendScheduledNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snooze:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled notifications that are ready to be sent.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $tolerance = config('snooze.sendTolerance');

        $notifications = ScheduledNotification::getPendingNotifications($tolerance);

        if (! $notifications->count()) {
            $this->info('No Scheduled Notifications need to be sent.');

            return;
        }

        $this->info('Starting Sending Scheduled Notifications');

        $bar = $this->output->createProgressBar(count($notifications));

        $bar->start();

        $this->info(sprintf('Sending %d scheduled notifications...', $notifications->count()));

        $notifications->each(function (ScheduledNotificationModel $notification) use ($bar) {
            $bar->advance();

            try {
                $notification->send();
            } catch (\Exception $e) {
                report($e);
                $this->error($e->getMessage());
            }
        });

        $bar->finish();

        $this->info('Finished Sending Scheduled Notifications');
    }
}
