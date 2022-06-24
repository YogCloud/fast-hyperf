<?php

declare(strict_types=1);

namespace YogCloud\Framework\Request;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

/**
 * Scenario validation
 * Trait ValidateSenceTrait.
 * @author stone
 * @method array scene(array $inputs = []) Scenario $input is validation data
 * @method array rules(array $inputs = []) Validation rule $input is validation data
 * @method void validateExtend(array $inputs, string $scene) Custom extension validation $input is validation data $scene is scene name
 * @method array messages() Rule error customization
 * @method array attributes() Rule attribute substitution
 */
trait ValidateSceneTrait
{
    /**
     * scene.
     * @var string
     */
    protected $validateScene;

    /**
     * Filter rule scenario.
     * @param array $rules Filter rule
     * @param array $inputs Request parameters
     * @return array Return to the rule after scenario
     */
    protected function sceneFormat(array $rules, array $inputs = []): array
    {
        if (! isset($this->validateScene)) {
            return $rules;
        }
        $scene = method_exists($this, 'scene') ? $this->scene($inputs) : [];
        if (! isset($scene[$this->validateScene])) {
            return $rules;
        }
        $sceneData = $scene[$this->validateScene];

        // Filter rule
        return array_filter($rules, function ($item, $key) use ($sceneData) {
            return in_array($key, $sceneData);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Scenario validation.
     * @param array $inputs Validation parameters
     * @param string $scene scene
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function validated(array $inputs, string $scene = ''): array
    {
        // scene
        $scene && $this->validateScene = $scene;
        // rule
        $rules                                  = [];
        method_exists($this, 'rules') && $rules = $this->sceneFormat($this->rules($inputs), $inputs);
        // Custom error messages
        $messages                                     = [];
        method_exists($this, 'messages') && $messages = $this->messages();
        // Custom attributes
        $attributes                                       = [];
        method_exists($this, 'attributes') && $attributes = $this->attributes();

        $validator = \Hyperf\Utils\ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);
        $validator = $validator->make($inputs, $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Custom validation
        method_exists($this, 'validateExtend') && $this->validateExtend($inputs, $this->validateScene);

        return $inputs;
    }
}
