<?php

namespace Raham\DDDCRUDGen;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeDDDCRUD extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:ddd {name : The name of the CRUD to generate}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate a DDD-structured CRUD with all necessary files';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $name = $this->argument('name');
    $model = Str::studly($name);

    $stubs_path = base_path('vendor/raham/dddcrudegen/stubs');

    $actionsPath = app_path("Domains/$model/Actions");
    $controllersPath = app_path('Domains/' . $model . '/Controllers');
    $dtoPath = app_path('Domains/' . $model . '/DTO');
    $models = app_path('Domains/' . $model . '/Models');
    $repositories = app_path('Domains/' . $model . '/Repositories');
    $contracts = app_path('Domains/' . $model . '/Repositories/Contracts');
    $requests = app_path('Domains/' . $model . '/Requests');
    $services = app_path('Domains/' . $model . '/Services');

    if (!File::exists(app_path("Domains"))) {
      File::makeDirectory(app_path("Domains"), 0755, true); // 0755 = permissions, true = recursive
    }

    if (!File::exists($actionsPath)) {
      File::makeDirectory($actionsPath, 0755, true);
    }

    if (!File::exists($controllersPath)) {
      File::makeDirectory($controllersPath, 0755, true);
    }

    if (!File::exists($dtoPath)) {
      File::makeDirectory($dtoPath, 0755, true);
    }

    if (!File::exists($models)) {
      File::makeDirectory($models, 0755, true);
    }

    if (!File::exists($repositories)) {
      File::makeDirectory($repositories, 0755, true);
    }

    if (!File::exists($contracts)) {
      File::makeDirectory($contracts, 0755, true);
    }

    if (!File::exists($requests)) {
      File::makeDirectory($requests, 0755, true);
    }

    if (!File::exists($services)) {
      File::makeDirectory($services, 0755, true);
    }

    function scaffoldAction($stubs_path, $model, $actionsPath)
    {
      $stub = File::get($stubs_path . '/actions' . '/Action.stub');

      $actions = [
        'CreateAction',
        'ReadAction',
        'UpdateAction',
        'DeleteAction'
      ];

      foreach ($actions as $actionName) {
        $action = str_replace('{{ class }}', $actionName, $stub);
        $action = str_replace('{{ namespace }}', "App\\Domains\\$model\\Actions", $action);

        File::put("{$actionsPath}/{$actionName}.php", $action);
      }
    }

    scaffoldAction(base_path('vendor/raham/dddcrudgen/stubs'), $model, $actionsPath);

    function scaffoldDTO($stubs_path, $model, $dtoPath)
    {
      $stub = File::get($stubs_path . '/dto' . '/DTO.stub');

      $dtos = [
        'CreateDTO',
        'ReadDTO',
        'UpdateDTO',
        'DeleteDTO'
      ];

      foreach ($dtos as $dtoName) {
        $dto = str_replace('{{ class }}', $dtoName, $stub);
        $dto = str_replace('{{ namespace }}', "App\\Domains\\$model\\DTO", $dto);

        File::put("{$dtoPath}/{$dtoName}.php", $dto);
      }
    }

    scaffoldDTO(base_path('vendor/raham/dddcrudgen/stubs'), $model, $dtoPath);



    $this->call('make:controller', [
      'name' => "App\\Domains\\$model\\Controllers\\{$model}Controller",
      '--api' => true
    ]);

    $this->call('make:model', [
      'name' => "App\\Domains\\$model\\Models\\{$model}",
      '--migration' => true,
    ]);

    $this->call('make:factory', [
      'name' => "{$model}Factory",
      '--model' => "App\\Domains\\$model\\Models\\{$model}",
    ]);

    $this->call('make:seeder', [
      'name' => "{$model}Seeder",
    ]);

    function scaffoldRepositoryInterface($stubs_path, $model)
    {
      $stub = File::get($stubs_path . '/repositories/contracts/RepositoryInterface.stub');

      $repositoryInterface = str_replace('{{ class }}', $model . 'RepositoryInterface', $stub);
      $repositoryInterface = str_replace('{{ namespace }}', "App\\Domains\\$model\\Repositories\\Contracts", $repositoryInterface);

      File::put("App/Domains/{$model}/Repositories/Contracts/{$model}RepositoryInterface.php", $repositoryInterface);
    }

    scaffoldRepositoryInterface(base_path('vendor/raham/dddcrudgen/stubs'), $model);

    function scaffoldRepository($stubs_path, $model)
    {
      $stub = File::get($stubs_path . '/repositories/Repository.stub');

      $repository = str_replace('{{ class }}', $model . 'Repository', $stub);
      $repository = str_replace('{{ namespace }}', "App\\Domains\\$model\\Repositories", $repository);
      $repository = str_replace('{{ interface }}', "Contracts\\PostRepositoryInterface", $repository);

      File::put("App/Domains/{$model}/Repositories/Contracts/{$model}Repository.php", $repository);
    }

    scaffoldRepository(base_path('vendor/raham/dddcrudgen/stubs'), $model);

    $this->call('make:request', [
      'name' => "App\\Domains\\$model\\Requests\\Store{$model}Request",
    ]);

    $this->call('make:request', [
      'name' => "App\\Domains\\$model\\Requests\\Update{$model}Request",
    ]);

    function scaffoldService($stubs_path, $model)
    {
      $stub = File::get($stubs_path . '/services/Service.stub');

      $service = str_replace('{{ class }}', $model . 'Service', $stub);
      $service = str_replace('{{ namespace }}', "App\\Domains\\$model\\Services", $service);

      File::put("App/Domains/{$model}/Services/{$model}Service.php", $service);
    }

    scaffoldService(base_path('vendor/raham/dddcrudgen/stubs'), $model);

    $this->info("DDD CRUD structure created for: $model");
  }
}
