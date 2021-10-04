<?php

declare(strict_types=1);

namespace YogCloud\Framework\Request;

use Hyperf\Validation\Request\FormRequest;

abstract class AbstractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
