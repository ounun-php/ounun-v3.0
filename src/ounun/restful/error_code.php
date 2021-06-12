<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */
declare (strict_types = 1);
namespace ounun\restful;


class error_code
{
    /**
     * GET:
     *   安全且幂等
     *     获取表示
     *     变更时获取表示（缓存）
     *
     * POST:
     *    不安全且不幂等
     *      使用服务端管理的（自动产生）的实例号创建资源
     *      创建子资源
     *      部分更新资源
     *      如果没有被修改，则不过更新资源（乐观锁）
     * PUT:
     *    不安全但幂等
     *      用客户端管理的实例号创建一个资源
     *      通过替换的方式更新资源
     *      如果未被修改，则更新资源（乐观锁）
     *
     * DELETE
     *     不安全但幂等
     *        删除资源
     *
     * @var string[]
     */
    const Maps = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',          // GET:表示已在响应中发出 POST:如果现有资源已被更改  PUT:如果已存在资源被更改 DELETE:资源已被删除
        201 => 'Created',     //                     POST:如果新资源被创建     PUT:如果新资源被创建
        202 => 'Accepted',    //                     POST:已接受处理请求但尚未完成（异步处理）
        203 => 'Non-Authoritative Information',
        204 => 'No Content',  // GET:资源有空表示
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',// GET|POST|PUT|DELETE:资源的URI已被更新
        302 => 'Found',
        303 => 'See Other',        // GET|POST|PUT|DELETE:其他（如，负载均衡）
        304 => 'Not Modified',     // GET|POST:资源未更改（缓存）
        305 => 'Use Proxy',
        306 => 'Unused',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',      // GET|POST|PUT|DELETE:指代坏请求（如，参数错误）
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',        // GET|POST|PUT|DELETE:资源不存在
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',   // GET|POST|PUT:服务端不支持所需表示
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',         // GET|PUT|DELETE:通用冲突
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',     // POST|PUT:前置条件失败（如执行条件更新时的冲突）
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',  // POST|PUT:接受到的表示不受支持
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',   // GET|POST|PUT|DELETE:通用错误响应
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',     // GET|POST|PUT|DELETE:服务端当前无法处理请求
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    ];
}
