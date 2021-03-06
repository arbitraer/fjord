<?php

namespace AwStudio\Fjord\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class FjordInstall extends Command
{
    use Traits\RolesAndPermissions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fjord:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This wizard will take you through the installation process';

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
    public function handle(Filesystem $filesystem)
    {
        // http://patorjk.com/software/taag/#p=display&h=1&v=0&f=Slant&t=Fjord%20Install
        $this->info("    ______ _                   __   ____              __          __ __");
        $this->info("   / ____/(_)____   _____ ____/ /  /  _/____   _____ / /_ ____ _ / // /");
        $this->info("  / /_   / // __ \ / ___// __  /   / / / __ \ / ___// __// __ `// // / ");
        $this->info(" / __/  / // /_/ // /   / /_/ /  _/ / / / / /(__  )/ /_ / /_/ // // /  ");
        $this->info("/_/  __/ / \____//_/    \__,_/  /___//_/ /_//____/ \__/ \__,_//_//_/   ");
        $this->info("    /___/                                                              ");

        $this->info("\n----- start -----\n");

        $this->vendorConfigs();
        $this->call('fjord:guard');

        $this->handleFjordPublishable();
        $this->handleFjordResources();

        $this->createDefaultRoles();
        $this->createDefaultPermissions();

        $this->publishDashboardController();

        $this->info("\n----- finished -----\n");

        $this->info('installation complete - run php artisan fjord:admin to create an admin user');
    }


    /**
     * Publish all relevant vendor config files and edit them as needed
     *
     * @return void
     */
    private function vendorConfigs()
    {
        $this->info('publishing vendor configs');

        $migrationFiles = array_keys(app()['migrator']->getMigrationFiles(database_path('migrations')));

        $this->callSilent('vendor:publish', [
            '--provider' => "Cviebrock\EloquentSluggable\ServiceProvider"
        ]);

        $this->callSilent('vendor:publish', [
            '--tag' => "translatable"
        ]);

        $replace = file_get_contents(config_path('translatable.php'));
        $replace = str_replace(
            "'fr',
        'es' => [
            'MX', // mexican spanish
            'CO', // colombian spanish
        ],",
            "'de'",
            $replace
        );
        File::put(config_path('translatable.php'), $replace);

        // If media migration exists, skip
        $mediaMatch = collect($migrationFiles)->filter(function($file) {
            return \Str::endsWith($file, 'create_media_table');
        })->first();
        if(! $mediaMatch) {
            $this->callSilent('vendor:publish', [
                '--provider' => "Spatie\MediaLibrary\MediaLibraryServiceProvider",
                '--tag' => "migrations"
            ]);
        }
        $this->callSilent('vendor:publish', [
            '--provider' => "Spatie\MediaLibrary\MediaLibraryServiceProvider",
            '--tag' => "config"
        ]);
        $content = file_get_contents(config_path('medialibrary.php'));
        $content = str_replace(
            'Spatie\MediaLibrary\Models\Media::class',
            'AwStudio\Fjord\Form\Database\Media::class',
            $content
        );
        File::put(config_path('medialibrary.php'), $content);


        $this->callSilent('vendor:publish', [
            '--provider' => "Spatie\Permission\PermissionServiceProvider",
            '--tag' => "migrations"
        ]);

        // migrate vendor tables
        if (\App::environment(['local', 'staging'])) {
            $this->callSilent('migrate');
        }else{
            $this->call('migrate');
        }


        // set correct namespace for translation models
        $search = "'translation_model_namespace' => null,";
        $replace = "'translation_model_namespace' => 'App\Models\Translations',";

        $str = file_get_contents(config_path('translatable.php'));

        if ($str !== false) {
            $str = str_replace($search, $replace, $str);
            file_put_contents(config_path('translatable.php'), $str);
        }
    }


    /**
     * Publish Fjord config and assets
     *
     * @return void
     */
    private function handleFjordPublishable()
    {
        $this->info('publishing fjord config & migrations');

        $this->callSilent('vendor:publish', [
            '--provider' => "AwStudio\Fjord\FjordServiceProvider"
        ]);

        // migrate tables
        if (\App::environment(['local', 'staging'])) {
            $this->callSilent('migrate');
        }else{
            $this->call('migrate');
        }
    }


    public function handleFjordResources()
    {
        if(is_dir(fjord_resource_path()) && fjord_resource_path() !== resource_path() ) {
            return;
        }
        
        $this->info('publishing fjord resources');
        // clear the config cache, otherwise, fjord_resource_path() will return
        // the resource path itself, which is present for shure
        $this->callSilent('config:clear');

        File::copyDirectory(fjord_path('publish/fjord'), resource_path('fjord'));
    }

    public function publishDashboardController()
    {
        if(!\File::exists( app_path('Http/Controllers/Fjord') )){
            \File::makeDirectory( app_path('Http/Controllers/Fjord') );
        }

        if(!File::exists(app_path('Http/Controllers/Fjord/DashboardController.php'))){
            $this->info('publishing DashboardController');

            File::copy(
                fjord_path('publish/controllers/DashboardController.php'),
                app_path('Http/Controllers/Fjord/DashboardController.php')
            );
        }
    }
}
