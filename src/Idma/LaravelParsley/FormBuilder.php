<?php

namespace Idma\LaravelParsley;

use Illuminate\Html\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder {
    /**
     * @type \Illuminate\Database\Eloquent\Model
     */
    protected $model;
    protected $modelName        = null;

    /**
     * @type Parsley
     */
    protected $parsley          = null;
    protected $validationRules  = [];
    protected $customAttributes = [];

    public static $abc = 1;

    /**
     * {@inheritdoc}
     */
    public function open(array $options = []) {
        if ($this->model && !isset($options['method'])) {
            $options['method'] = $this->model->getAttribute('id') ? 'put' : 'post';
        }

        $method = strtoupper(array_get($options, 'method', 'post'));

        // We need to extract the proper method from the attributes. If the method is
        // something other than GET or POST we'll use POST since we will spoof the
        // actual method since forms don't support the reserved methods in HTML.
        $attributes['method'] = $this->getMethod($method);

        $attributes['action'] = $this->getAction($options);

        $attributes['accept-charset'] = 'UTF-8';

        // If the method is PUT, PATCH or DELETE we will need to add a spoofer hidden
        // field that will instruct the Symfony request to pretend the method is a
        // different method than it actually is, for convenience from the forms.
        $append = $this->getAppendage($method);

        if (isset($options['files']) && $options['files']) {
            $options['enctype'] = 'multipart/form-data';
        }

        // Finally we're ready to create the final form HTML field. We will attribute
        // format the array of attributes. We will also add on the appendage which
        // is used to spoof requests for this PUT, PATCH, etc. methods on forms.
        $attributes = array_merge($attributes, array_except($options, $this->reserved));

        // Finally, we will concatenate all of the attributes into a single string so
        // we can build out the final form open statement. We'll also append on an
        // extra value for the hidden _method field if it's needed for the form.
        $attributes = $this->html->attributes($attributes);

        return '<form'.$attributes.'>'.$append;
    }

    /**
     * {@inheritdoc}
     */
    public function model($model, array $options = []) {
        $this->setModel($model);

        return $this->open($options);
    }

    public function openModel($model, array $options = []) {
        return $this->model($model, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function label($name, $value = null, $options = []) {
        $this->labels[] = $name;

        $options = $this->html->attributes($options);

        $value = e($this->formatLabel($name, $value));

        $for = ($this->modelName && !starts_with($name, '_')) ? $this->modelName.'-'.$name : $name;

        return '<label for="'.$for.'"'.$options.'>'.$value.'</label>';
    }

    public function helpBlock($value, array $options = []) {
        if (isset($options['class'])) {
            $options['class'] = 'help-block '.$options['class'];
        } else {
            $options['class'] = 'help-block';
        }

        return '<span'.$this->html->attributes($options).'>'.$value.'</span>';
    }

    public function input($type, $name, $value = null, $options = [])
    {
        $options = array_merge($options, $this->createParsleyRulesForField($name));
        return parent::input($type, $name, $value, $options);
    }

    /**
     * @param $name
     *
     * @return array
     */
    protected function createParsleyRulesForField($name) {
        if (isset($this->validationRules[$name]) && !starts_with($name, '_')) {
            $rules = explode('|', $this->validationRules[$name]);
            $parsleyRules = [];

            $parsleyRules = array_merge($parsleyRules, $this->parsley->convertRules($name, $rules, $this->customAttributes));

            return $parsleyRules;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
//    public function getIdAttribute($name, $attributes) {
//        $id = null;
//
//        if (array_key_exists('id', $attributes)) {
//            $id = $attributes['id'];
//        } else {
//            $id = $name;
//        }
//
//        if ($this->modelName && $id && !starts_with($id, '_')) {
//            return $this->modelName.'-'.$id;
//        }
//
//        return null;
//    }

    public function setModel($model) {
        $this->model = $model;
        $this->modelName = strtolower((new \ReflectionClass($this->model))->getShortName());

        if (method_exists($this->model, 'getParsleyRules')) {
            $this->validationRules = $this->model->getParsleyRules();

            if (property_exists($this->model, 'getParsleyCustomAttributes')) {
                $_model = $this->model;
                $this->customAttributes = $_model::$customAttributes;
            }

            $this->parsley = new Parsley($this->customAttributes);
        }
    }

    /**
     * Gets the short model name.
     *
     * @return string
     */
    public function getModelName() {
        return $this->modelName;
    }

    public function name() {
        return $this->getModelName();
    }
}
