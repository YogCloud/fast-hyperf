<?php

declare(strict_types=1);

namespace YogCloud\Framework\Request;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

/**
 * 场景验证
 * Trait ValidateSenceTrait.
 * @author stone
 * @method array scene(array $inputs = []) 场景 $input为验证数据
 * @method array rules(array $inputs = []) 验证规则 $input为验证数据
 * @method void validateExtend(array $inputs, string $scene) 自定义扩展验证 $input为验证数据 $scene为场景名称
 * @method array messages() 规则错误自定义
 * @method array attributes() 规则属性替换
 */
trait ValidateSceneTrait
{
    /**
     * 场景.
     * @var string
     */
    protected $validateScene;

    /**
     * 过滤规则场景化.
     * @param array $rules 过滤规则
     * @param array $inputs 请求参数
     * @return array 返回场景化后的规则
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

        ## 过滤规则
        return array_filter($rules, function ($item, $key) use ($sceneData) {
            return in_array($key, $sceneData);
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * 场景验证
     * @param array $inputs 验证参数
     * @param string $scene 场景
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @return array 验证参数
     */
    protected function validated(array $inputs, string $scene = ''): array
    {
        ## 场景
        $scene && $this->validateScene = $scene;
        ## 规则
        $rules                                  = [];
        method_exists($this, 'rules') && $rules = $this->sceneFormat($this->rules($inputs), $inputs);
        ## 自定义错误信息
        $messages                                     = [];
        method_exists($this, 'messages') && $messages = $this->messages();
        ## 自定义属性
        $attributes                                       = [];
        method_exists($this, 'attributes') && $attributes = $this->attributes();

        $validator = \Hyperf\Utils\ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);
        $validator = $validator->make($inputs, $rules, $messages, $attributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        ## 自定义验证
        method_exists($this, 'validateExtend') && $this->validateExtend($inputs, $this->validateScene);

        return $inputs;
    }
}
