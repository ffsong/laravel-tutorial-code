<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\ApiResponse;

class ApiException extends Exception
{
    use ApiResponse;

    /**
     * 转换异常为 HTTP 响应
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
//        return response($this->getMessage() ?: '发生异常啦');

        return $this->failed($this->getMessage(),401);
    }

}
