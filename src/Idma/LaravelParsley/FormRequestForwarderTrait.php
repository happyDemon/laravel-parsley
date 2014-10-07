<?php

namespace Idma\LaravelParsley;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FromRequestForwarderTrait
 *
 * @package Idma\LaravelParsley
 */
trait FormRequestForwarderTrait
{
    public function validate()
    {
        /** @type FormRequest $this */
        \View::share('_ilp_request', $this);

        if ($this->isMethod('POST')) {
            parent::validate();
        }
    }
}
