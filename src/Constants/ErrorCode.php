<?php

declare(strict_types=1);

namespace YogCloud\Framework\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * Error code
 * 3位HTTP码 + 6位业务码[前3位为模块，后3位为业务]
 * 有其它错误码需求，即使补充
 * 业务模块码:
 * 100  -  公共模块
 * 100  -  授权模块
 * 200  -  通讯录模块
 * 300  -  外部联系人模块
 * 301  -  外部联系人 - 高级属性模块
 * 400  -  会话内容存档模块.
 * @Constants
 * @method static string getMessage(int $code)  获取错误码信息
 * @method static int getHttpCode(int $code) 获取错误码的httpCode
 */
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("token失效")
     * @HttpCode("401")
     */
    const TOKEN_INVALID = 100001;

    /**
     * @Message("用户或密码错误")
     * @HttpCode("401")
     */
    const AUTH_LOGIN_FAILED = 100002;

    /**
     * @Message("非法token")
     * @HttpCode("401")
     */
    const AUTH_TOKEN_INVALID = 100003;

    /**
     * @Message("token过期")
     * @HttpCode("401")
     */
    const AUTH_SESSION_EXPIRED = 100004;

    /**
     * @Message("未认证,没有token")
     * @HttpCode("401")
     */
    const AUTH_UNAUTHORIZED = 100005;

    /**
     * @Message("认证失败")
     * @HttpCode("401")
     */
    const AUTH_FAILED = 100006;

    /**
     * @Message("没有权限")
     * @HttpCode("403")
     */
    const ACCESS_DENIED = 100007;

    /**
     * @Message("拒绝客户端请求")
     * @HttpCode("403")
     */
    const ACCESS_REFUSE = 100008;

    /**
     * @Message("禁止重复操作")
     * @HttpCode("403")
     */
    const NO_REPETITION_OPERATION = 100009;

    /**
     * @Message("客户端错误")
     * @HttpCode("400")
     */
    const BAD_REQUEST = 100010;

    /**
     * @Message("非法的Content-Type头")
     * @HttpCode("401")
     */
    const INVALID_CONTENT_TYPE = 100011;

    /**
     * @Message("资源未找到")
     * @HttpCode("404")
     */
    const URI_NOT_FOUND = 100012;

    /**
     * @Message("非法的参数")
     * @HttpCode("422")
     */
    const INVALID_PARAMS = 100013;

    /**
     * @Message("服务器异常")
     * @HttpCode("500")
     */
    const SERVER_ERROR = 100014;

    /**
     * @Message("服务器异常(third-party-api)")
     * @HttpCode("500")
     */
    const THIRD_API_ERROR = 100015;

    /**
     * @Message("请求方法错误")
     * @HttpCode("405")
     */
    const INVALID_HTTP_METHOD = 100016;
}
