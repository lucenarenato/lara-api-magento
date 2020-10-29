<?php

namespace App\Console\Commands;

use App\Jobs\MagentoDisableProductJob;
use Illuminate\Console\Command;

class magentoDisableProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'magento:disableProduct {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Disable Product on Magento';

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
        dispatch(new MagentoDisableProductJob($this->argument('id')));
    }
}
