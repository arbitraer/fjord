<?php

namespace AwStudio\Fjord\Commands;

use Illuminate\Console\Command;

class FjordCrudPermissions extends Command
{
    use Traits\RolesAndPermissions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fjord:crud-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        $this->createCrudPermissions();
    }
}
