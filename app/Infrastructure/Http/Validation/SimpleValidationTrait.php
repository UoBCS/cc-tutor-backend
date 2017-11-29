<?php

namespace App\Infrastructure\Http\Validation;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Validator;

/**
 * A utility trait for dealing with one-attribute validation rules
 */
trait SimpleValidationTrait
{
    /**
     * The attribute rules so far
     *
     * @var array
     */
    private $attributeRules;

    /**
     * The attribute to validate
     *
     * @var string
     */
    private $attributeToValidate;

    /**
     * Add the array rule
     *
     * @param  string $attribute
     * @return object
     */
    public function array($attribute = null)
    {
        if (!is_null($attribute)) {
            $this->attributeToValidate = $attribute;
        }

        $this->attributeRules[] = 'array';

        return $this;
    }

    /**
     * Add the boolean rule
     *
     * @param  string $attribute
     * @return object
     */
    public function boolean($attribute = null)
    {
        if (!is_null($attribute)) {
            $this->attributeToValidate = $attribute;
        }

        $this->attributeRules[] = 'boolean';

        return $this;
    }

    /**
     * Add the required rule
     *
     * @param  string $attribute
     * @return object
     */
    public function required($attribute = null)
    {
        if (!is_null($attribute)) {
            $this->attributeToValidate = $attribute;
        }

        $this->attributeRules[] = 'required';

        return $this;
    }

    /**
     * Get the request input after validation
     *
     * @param  string $attribute
     * @return \Illuminate\Http\Request|string|array
     */
    public function get($attribute = null)
    {
        if (!is_null($attribute)) {
            $this->attributeToValidate = $attribute;
        }

        $validator = Validator::make(request()->all(), [
            $this->attributeToValidate => implode('|', $this->attributeRules),
        ]);

        if ($validator->fails()) {
            throw new UnprocessableEntityHttpException($validator->errors()->toJson());
        }

        return request($this->attributeToValidate);
    }
}
