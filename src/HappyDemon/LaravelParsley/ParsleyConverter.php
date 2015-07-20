<?php

namespace HappyDemon\LaravelParsley;

use Illuminate\Translation\Translator;

class ParsleyConverter {
    use \Illuminate\Console\AppNamespaceDetectorTrait;

    protected $rules            = [];
    protected $customAttributes = [];

    /**
     * @type Translator
     */
    protected $translator       = null;

    public function __construct($formRequest=null)
    {
        if($formRequest != null && !is_object($formRequest))
        {
            $class = $this->getAppNamespace() . 'Http\Requests\\'.$formRequest;
            $formRequest = new $class;
        }

        if ($formRequest && method_exists($formRequest, 'rules')) {
            $this->rules = $formRequest->rules();

            if (method_exists($formRequest, 'customAttributes')) {
                $this->customAttributes = $formRequest->customAttributes();
            }

            $this->translator = app()['translator'];
        }
    }

    public function getFieldRules($field)
    {
        $rules = [];
        if (isset($this->rules[$field])) {
            $rawRules = explode('|', $this->rules[$field]);

            $rules = array_merge($rules, $this->convertRules($field, $rawRules));
        }

        return $rules;
    }

    public function convertRules($attribute, $rules)
    {
        $attrs   = [];
        $message = null;

        $date_format = null;

        foreach ($rules as $rule) {
            list($rule, $params) = explode(':', $rule . ':');

            $params = explode(',', $params);

            $parsleyRule = $rule;

            $isNumeric = $this->hasNumericRule($rules);
            $message = $this->getMessage($attribute, $rule, $rules);

            switch ($rule) {
                case 'accepted':
                case 'required':
                    break;

                case 'email':
                    $parsleyRule = 'type';
                    $params = 'email';
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

                case 'integer':
                    $parsleyRule = 'integer';
                    break;

                case 'url':
                    $parsleyRule = 'type';
                    $params = 'url';
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

                case 'different':
                    $parsleyRule = 'different';
                    $message = str_replace(':other', $params[0], $message);
                    $params = '#'.$params[0];
                    break;

                case 'date_format':
                    $parsleyRule = 'dateformat';
                    $replace = [
                        // Day
                        'd' => 'DD', 'D' => 'ddd', 'j' => 'D', 'l' => 'DDDD',
                        'N' => 'E', 'S' => '', 'w' => 'W', 'z' => 'DDD',
                        // Week
                        'W' => 'w',
                        // Month
                        'F' => 'MMMM', 'm' => 'MM', 'M' => 'MMM', 'n' => 'M', 't' => '',
                        // Year
                        'L' => '', 'o' => 'YYYY', 'Y' => 'YYYY', 'y' => 'YY',
                        // Time
                        'a' => 'a', 'A' => 'A', 'B' => '', 'g' => 'h', 'G' => 'H',
                        'h' => 'hh', 'H' => 'HH', 'i' => 'i', 's' => 's', 'u' => ''
                    ];
                    $params = str_replace(array_keys($replace), array_values($replace), $params[0]);
                    $date_format = $params;
                    $message = str_replace(':format', $params, $message);
                break;
                case 'before':
                case 'after':
                    $params = $params[0];
                        if($date_format !== null)
                        {
                            $params .= '|-|'.$date_format;
                        }
                    break;
                case 'in':
                case 'not_in':
                    $parsleyRule = camel_case($rule).'List';
                    $params = implode(',', $params);
                    break;
                default:
                    $message = null;
                    break;
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

    protected function getMessage($attribute, $currentRule, $rules)
    {
        $lowerRule = snake_case($currentRule);
        $customKey = "validation.custom.{$attribute}.{$lowerRule}";

        $customMessage = $this->translator->trans($customKey);

        if ($customMessage !== $customKey) {
            return $customMessage;
        } else if (in_array($currentRule, ['size', 'between', 'min', 'max'])) {
            if ($this->hasNumericRule($rules)) {
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

    protected function hasNumericRule($rules) {
        foreach ($rules as $rule) {
            list($rule, $params) = explode(':', $rule . ':');

            if ($this->isNumericRule($rule)) {
                return true;
            }
        }

        return false;
    }

    protected function isNumericRule($rule) {
        return in_array(ucfirst($rule), ['Integer', 'Numeric']);
    }
}
