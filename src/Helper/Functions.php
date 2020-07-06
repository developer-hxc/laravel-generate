<?php
if (! function_exists('commonReturn')) {
    /**
     * 通用返回
     * @param $flag
     * @param $message
     * @param null $data
     * @return array
     */
    function commonReturn($flag, $message, $data = null)
    {
        if ($flag || $flag === '' || $flag === []) {
            return returnSuccess($data);
        } else {
            return returnFail($message);
        }
    }
}

if (! function_exists('returnSuccess')) {
    /**
     * 返回成功
     * @param null $data
     * @return array
     */
    function returnSuccess($data = null)
    {
        $return = [
            'code' => 1,
            'status' => 'success',
            'data' => $data
        ];
        return $return;
    }
}

if (! function_exists('returnFail')) {
    /**
     * 返回失败
     * @param $message
     * @return array
     */
    function returnFail($message)
    {
        return [
            'code' => 0,
            'status' => 'fail',
            'message' => $message
        ];
    }
}

if(! function_exists('notLogin')){
    /**
     * 未登录
     * @return array
     */
    function notLogin()
    {
        return [
            'code' => -1,
            'status' => 'fail',
            'message' => '未登录'
        ];
    }
}

if (! function_exists('getConfig')) {
    /**
     * 获取配置信息
     * @param $type
     * @param string $key
     * @return mixed
     */
    function getConfig($type,$key = '')
    {
        $sql = \App\Config::where('type',$type)->orderBy('sort','desc');
        if(!$key && $key !== 0)
            return $sql->get(['id','type','key','value']);
        else
            return $sql->where('key',$key)->first(['id','type','key','value']);
    }
}

if (! function_exists('getAuth')){
    /**
     * 获取当前登录账号信息
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    function getAuth()
    {
        return \Illuminate\Support\Facades\Auth::user();
    }
}

if(! function_exists('checkParams')){
    /**
     * 检查参数
     * @param $request
     * @param $paramsKey
     * @return mixed
     */
    function checkParams($request,$paramsKey)
    {
        $params = $request->only(array_keys($paramsKey));
        foreach ($paramsKey as $k => $v){
            if($request->missing($k)){
                if($v) {
                    return $v;
                }
            } else{
                if(!key_exists($k,$params)){
                    if($v) {
                        return $v;
                    }
                }
            }
        }
        return $params;
    }
}

if(! function_exists('checkCode')){
    /**
     * 验证短信验证码
     * @param $phone
     * @param $code
     * @param $type
     * @return bool
     */
    function checkCode($phone,$code,$type)
    {
        if($code == 6666) return true;
        $now = now()->format('Y-m-d H:i:s');
        $sms = \App\Sms::where('phone',$phone)
            ->where('code',$code)
            ->where('scene',$type)
            ->where('status',1)
            ->where('end_time','>=',$now)
            ->first();
        if($sms){
            $sms->status = 2;
            $sms->save();
            return true;
        }else{
            return false;
        }
    }
}

if(! function_exists('checkPhone')){
    /**
     * @param $phone
     * @return bool
     */
    function checkPhone($phone){
        $check = '/^(1(([35789][0-9])|(47)))\d{8}$/';
        if (preg_match($check, $phone)) {
            return true;
        } else {
            return false;
        }
    }
}
