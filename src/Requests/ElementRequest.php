<?php

namespace Moonlight\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ElementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        foreach ($this->properties as $propertyName => $property) {
            $rules[$propertyName] = array_keys($property->getRules());
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $rules = [];

        foreach ($this->properties as $propertyName => $property) {
            foreach ($property->getRules() as $rule => $message) {
                $rules[$propertyName][] = $rule;

                if (strpos($rule, ':')) {
                    [$name, $value] = explode(':', $rule, 2);
                    $messages[$propertyName.'.'.$name] = $message;
                } else {
                    $messages[$propertyName.'.'.$rule] = $message;
                }
            }
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
    }
}
