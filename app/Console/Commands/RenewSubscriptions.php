<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PaymentService;

class RenewSubscriptions extends Command {

    private $payment_service;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'renew:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for subscription renewals';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PaymentService $payment_service) {
        parent::__construct();
        $this->payment_service = $payment_service;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->payment_service->renewSubscriptions();
    }

}
