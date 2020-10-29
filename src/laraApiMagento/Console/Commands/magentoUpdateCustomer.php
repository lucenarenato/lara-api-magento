<?php

namespace App\Console\Commands;

use App\Jobs\MagentoUpdateCustomerJob;
use Illuminate\Console\Command;

class magentoUpdateCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:updateCustomer {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Customer on Magento';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dispatch(new MagentoUpdateCustomerJob($this->argument('id')));
    }
}