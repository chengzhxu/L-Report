<?php


namespace App\Http\Controllers\Report;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ReportAbstract extends BaseController{

    /**
     * @var ServerRequest|mixed
     */
    protected $request;

    public function __construct(Request $request)
    {
        if(!isset($_SESSION['USERNAME'])){
            return $this->toJson(300, [], '用户信息获取失败，请重新登录');
        }
        $this->request = $request;
    }

    /**
     * @return ServerRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return JsonResponse
     *
     * @param integer $status
     * @param integer|200 $code
     * @param array|null $data
     * @param string|'' $message
     * @return Response
     */
    public function toJson($code = 200, $data = null, $message = ''){

        return new JsonResponse(['code' => $code, 'data' => $data, 'message' => $message]);
    }
}