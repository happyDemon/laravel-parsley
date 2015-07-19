<?php

namespace HappyDemon\LaravelParsley;


trait FormTrait
{
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
    public function input($type, $name, $value = null, $options = [])
    {
        if ($this->parsley != null)
        {
            $options = array_merge($options, $this->parsley->getFieldRules($name));
        }

        return parent::input($type, $name, $value, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function textarea($name, $value = null, $options = [])
    {
        if ($this->parsley != null)
        {
            $options = array_merge($options, $this->parsley->getFieldRules($name));
        }

        return parent::textarea($name, $value, $options);
    }

    public function select($name, $list = [], $selected = null, $options = [])
    {
        if ($this->parsley != null)
        {
            $options = array_merge($options, $this->parsley->getFieldRules($name));
        }

        return parent::select($name, $list, $selected, $options);
    }
}