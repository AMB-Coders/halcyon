<?php

namespace App\Modules\History\Providers;

//use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
//use App\Modules\History\Facades\History as HistoryFacade;
//use App\Modules\History\Models\History;

class ModuleServiceProvider extends ServiceProvider
{
	/**
	 * Module name
	 *
	 * @var string
	 */
	public $name = 'history';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerViews();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		//AliasLoader::getInstance()->alias('History', HistoryFacade::class);
	}

	/*public function register()
	{
		$app = $this->app;

		// Register route service provider
		$app->register(RouteServiceProvider::class);

		//$app->bind('History', History::class);
	}*/

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/' . $this->name);

		$sourcePath = __DIR__ . '/../Resources/views';

		$this->publishes([
			$sourcePath => $viewPath
		],'views');

		$this->loadViewsFrom(array_merge(array_map(function ($path)
		{
			return $path . '/modules/' . $this->name;
		}, config('view.paths')), [$sourcePath]), $this->name);
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/' . $this->name);

		if (!is_dir($langPath))
		{
			$langPath = __DIR__ . '/../Resources/lang';
		}

		$this->loadTranslationsFrom($langPath, $this->name);
	}
}
