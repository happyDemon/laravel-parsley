<?php

namespace Idma\LaravelParsley;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class FromRequestForwarderTrait
 *
 * @package Idma\LaravelParsley
 */
trait UseParsleyValidationTrait
{
    public function validate()
    {
        /** @type FormRequest $this */
        \View::share('_ilp_request', $this);

        if ([] != $this->all()) {
            call_user_func('parent::validate');
        } else {
            if (!call_user_func([$this, 'passesAuthorization'])) {
                call_user_func([$this, 'failedAuthorization']);
            }
        }
    }
}
