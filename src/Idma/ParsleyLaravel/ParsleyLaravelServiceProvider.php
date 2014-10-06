<?php

namespace Idma\LaravelParsley;

use Illuminate\Html\HtmlServiceProvider;

class ParsleyLaravelServiceProvider extends HtmlServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
		$this->package('idma/parsley-laravel');

		parent::register();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function registerFormBuilder()
	{
		$this->app->bindShared('form', function($app)
		{
			$form = new FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());

			return $form->setSessionStore($app['session.store']);
		});
	}
}
