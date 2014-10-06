<?php

namespace Idma\LaravelParsley;

use Illuminate\Translation\Translator;

class Parsley {
    /**
     * @type Translator
     */
    protected $translator       = null;
    protected $customAttributes = [];

    public function __construct(array $customAttributes = [])
    {
        $this->customAttributes = $customAttributes;
        $this->translator = app()['translator'];
    }

    public function convertRules($attribute, $rules)
    {
        $attrs   = [];
        $message = null;

        foreach ($rules as $rule) {
            list($rule, $params) = explode(':', $rule . ':');

            $params = explode(',', str_replace(' ', '', $params));

            $parsleyRule = $rule;

            $isNumeric = $this->isNumericRule($rule);
            $message = $this->getMessage($attribute, $rule);

            switch ($rule) {
                case 'required':
                case 'email':
                    break;

                case 'min':
                    if (!$isNumeric) {
                        $parsleyRule = 'minlength';
                    }

                    $message = str_replace(':min', $params[0], $message);
                    break;

                case 'max':
                    if (!$isNumeric) {
                        $parsleyRule = 'maxlength';
                    }

                    $message = str_replace(':max', $params[0], $message);
                    break;

                case 'between':
                    $parsleyRule = 'length';
                    $params      = str_replace([':min', ':max'], $params, '[:min,:max]');
                    $message     = str_replace([':min', ':max'], $params, $message);
                    break;

                case 'alpha_num':
                    $parsleyRule = 'alphanum';
                    $params = '/^\d[a-zа-яё\-\_]+$/i';
                    break;

                case 'alpha_dash':
                    $parsleyRule = 'pattern';
                    $params = '/^\d[a-zа-яё\-\_]+$/i';
                    break;

                case 'alpha':
                    $parsleyRule = 'pattern';
                    $params = '/^[a-zа-яё]+$/i';
                    break;

                case 'regex':
                    $parsleyRule = 'pattern';
                    break;

                case 'confirmed':
                    $parsleyRule = 'equalto';
                    $params = "#{$attribute}_confirmation";
                    break;

                default:
                    $message = null;
            }

            if ($message) {
                if (is_array($params) && count($params) == 1) {
                    $params = $params[0];
                }

                $attrs['data-parsley-' . $parsleyRule]              = $params;
                $attrs['data-parsley-' . $parsleyRule . '-message'] = str_replace(':attribute', $this->getAttribute($attribute), $message);
            }

            $message = null;
        }

        return $attrs;
    }

    protected function getMessage($attribute, $rule)
    {
        $lowerRule = snake_case($rule);
        $customKey = "validation.custom.{$attribute}.{$lowerRule}";

        $customMessage = $this->translator->trans($customKey);

        if ($customMessage !== $customKey) {
            return $customMessage;
        } else if (in_array($rule, ['size', 'between', 'min', 'max'])) {
            if ($this->isNumericRule($rule)) {
                $key = "validation.{$lowerRule}.numeric";
            } else {
                $key = "validation.{$lowerRule}.string";
            }

            return $this->translator->trans($key);
        }

        $key = "validation.{$lowerRule}";

        if ($key != ($value = $this->translator->trans($key))) {
            return $value;
        }

        return '';
    }

    /**
     * Get the displayable name of the attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function getAttribute($attribute)
    {
        if (isset($this->customAttributes[$attribute])) {
            return $this->customAttributes[$attribute];
        }

        $key = "validation.attributes.{$attribute}";

        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        } else {
            return str_replace('_', ' ', snake_case($attribute));
        }
    }

    protected function isNumericRule($rule) {
        return in_array(ucfirst($rule), ['Integer', 'Numeric']);
    }
}
