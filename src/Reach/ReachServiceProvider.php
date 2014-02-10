<?php namespace Reach;

use Reach\Reach;
use Illuminate\Redis\Database as Redis;
use Illuminate\Support\ServiceProvider;

class ReachServiceProvider extends ServiceProvider {

	protected $defer = false;

	public function boot() 
	{

	}

	public function register()
	{
		$this->package('jesse/reach');

		$this->app['reach'] = $this->app->share(function($app){
			$connections = $app['config']->get('database.redis');
			return new Reach(new Redis($connections));
		});
	}

}
