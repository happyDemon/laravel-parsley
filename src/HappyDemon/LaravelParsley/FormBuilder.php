<?php

namespace HappyDemon\LaravelParsley;

use Collective\Html\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder
{
    protected $modelName = null;

    /**
     * @type ParsleyConverter
     */
    protected $parsley = null;

    /**
     * {@inheritdoc}
     */
    public function open(array $options = [])
    {
        $this->reserved[] = 'request';
        $this->parsley = new ParsleyConverter(array_get($options, 'request', null));

        return parent::open($options);
    }

    /**
     * {@inheritdoc}
     */
    public function model($model, array $options = [])
    {
        $this->setModel($model);

        return $this->open($options);
    }

    public function openModel($model, array $options = [])
    {
        return $this->model($model, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function label($name, $value = null, $options = [])
    {
        $this->labels[] = $name;

        $options = $this->html->attributes($options);

        $value = e($this->formatLabel($name, $value));

        $for = ($this->modelName && !starts_with($name, '_')) ? $this->modelName . '-' . $name : $name;

        return '<label for="' . $for . '"' . $options . '>' . $value . '</label>';
    }

    /**
     * Create a Bootstrap-like help block.
     *
     * @param  string $value
     * @param  array $options
     *
     * @return string
     */
    public function helpBlock($value, array $options = [])
    {
        if (isset($options['class'])) {
            $options['class'] = 'help-block ' . $options['class'];
        } else {
            $options['class'] = 'help-block';
        }

        return '<span' . $this->html->attributes($options) . '>' . $value . '</span>';
    }

    /**
     * {@inheritdoc}
     */
    public function input($type, $name, $value = null, $options = [])
    {
        if($this->parsley != null)
            $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::input($type, $name, $value, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function textarea($name, $value = null, $options = [])
    {
        if($this->parsley != null)
            $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::textarea($name, $value, $options);
    }

    public function select($name, $list = [], $selected = null, $options = [])
    {
        if($this->parsley != null)
            $options = array_merge($options, $this->parsley->getFieldRules($name));

        return parent::select($name, $list, $selected, $options);
    }

    /**
     * Gets the short model name.
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->modelName;
    }

    public function name()
    {
        return $this->getModelName();
    }
}
