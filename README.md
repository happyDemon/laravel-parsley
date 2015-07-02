laravel-parsley
===============

Converts [FormRequest](http://laravel.com/docs/5.0/validation#form-request-validation) rules to [Parsley](http://parsleyjs.org/) rules.


## Install

If you have previously set up `LaravelCollective/Html` or `Illuminate/Html` you can remove its service provider from `app/config`

in `app/config` add the following under service providers: 

`HappyDemon\LaravelParsley\LaravelParsleyServiceProvider`

If you haven't already, add these facades:

    'Form' => 'Collective\Html\FormFacade',
    'Html' => 'Collective\Html\HtmlFacade',
    
## Useage

All that's needed is for you to supply the name of the `FormRequest` in the `request` key when opening a form.

    Form::open(['request' => 'YourFormRequestClass'])
    Form::model(['request' => 'YourFormRequestClass'])
    
Lastly you should include parsley's scripts on the page and activate parsley for your form.

easy enough don't you think?

### Validation rules

Implemented| Not implemented |
-----------|-----------------|
 required  |  accepted       |
 email     |  after:(date)   |
 min       |  before:(date)  |
 max       |  before:(date)  |
 between   |  date_format    |
 integer   |  different      |
 url       |  in             |
 alpha_num |  not_in         |
 alpha_dash|  ip_address     |
 alpha     |                 |
 regex     |                 |
 confirmed |                 |
