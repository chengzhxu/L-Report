<?php


namespace App\Http\Controllers\Report;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class ReportAbstract extends BaseController{

    /**
     * @var ServerRequest|mixed
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function guard()
    {
        return Auth::guard('report');
    }

    protected function getAppid(){
        $user = $this->guard()->user();
        return isset($user['appid']) ? $user['appid'] : 0;
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
        if(!$message){
            $message = get_code_msg($code);
        }
        return new JsonResponse(['code' => $code, 'data' => $data, 'message' => $message]);
    }
}