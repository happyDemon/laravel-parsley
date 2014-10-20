<?php

namespace Idma\LaravelParsley;

use Illuminate\Html\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder {
    protected $modelName = null;

    /**
     * @type ParsleyConverter
     */
    protected $parsley   = null;

    /**
     * {@inheritdoc}
     */
    public function open(array $options = []) {
        $this->parsley = new ParsleyConverter();

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

    /**
     * Create a Bootstrap-like help block.
     *
     * @param  string $value
     * @param  array  $options
     *
     * @return string
     */
    public function helpBlock($value, array $options = []) {
        if (isset($options['class'])) {
            $options['class'] = 'help-block '.$options['class'];
        } else {
            $options['class'] = 'help-block';
        }

        return '<span'.$this->html->attributes($options).'>'.$value.'</span>';
    }

    /**
     * {@inheritdoc}
     */
    public function input($type, $name, $value = null, $options = [])
    {
        $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::input($type, $name, $value, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function textarea($name, $value = null, $options = [])
    {
        $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::textarea($name, $value, $options);
    }

    public function select($name, $list = [], $selected = null, $options = [])
    {
        $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::select($name, $list, $selected, $options);
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
