<?php

namespace HappyDemon\LaravelParsley;

use Collective\Html\HtmlServiceProvider;

class LaravelParsleyServiceProvider extends HtmlServiceProvider
{
	/**
	 * {@inheritdoc}
	 */
	public function register()
	{
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
