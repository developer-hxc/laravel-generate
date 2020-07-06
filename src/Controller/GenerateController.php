<?php
namespace HXC\LaravelGenerate\Controller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

class GenerateController extends Controller
{
    /**
     * GenerateController constructor.
     * @param Request $request
     * @throws \Exception
     */
    public function __construct(Request $request)
    {
        $ips = explode(',',env('GENERATE_IPS','127.0.0.1'));
        if($request->path() === 'hxc/generate' && !in_array($request->ip(),$ips)){
            throw new \Exception('没有权限访问');
        }
    }

    /**
     * 生成页面视图
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('hxc::generate');
    }

    /**
     * 获取数据表名称
     * @return array
     */
    public function getTables()
    {
        $prefix = config('database.connections.mysql.prefix');
        $database = env('DB_DATABASE','');
        if(!$database) {
            return returnFail($data);
        }
        $tables_arr = DB::select("show tables");
        $res = [];
        if ($tables_arr) {
            foreach ($tables_arr as $k => $v) {
                $database_key = 'Tables_in_' . $database;
                if($prefix){
                    $table_name = str_replace($prefix, '', $v->$database_key);
                }else{
                    $table_name = $v->$database_key;
                }
                $res[] = [
                    'value' => $table_name,
                    'label' => $table_name
                ];
            }
        }
        return $this->commonReturn($res, '没有数据表，请添加数据表后刷新重试',$res);
    }

    /**
     * 获取数据表字段
     * @param Request $request
     * @return array
     */
    public function getTableFieldsData(Request $request)
    {
        $table = $request->table;
        $prefix = config('database.connections.mysql.prefix');
        $res = [];
        $data = DB::select("SHOW FULL COLUMNS FROM `{$prefix}{$table}`");
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $res[] = [
                    'name' => $v->Field, //字段名
                    'comment' => $v->Comment, //注释
                    'type' => $v->Type, //类型
                    'label' => '', //名称
                    'curd' => [], //操作
                    'business' => '', //业务类型
                    'search' => false, //业务类型
                    'require' => $v->Null == 'NO', //必填
                    'length' => preg_replace('/\D/s', '', $v->Type), //字段长度，不严谨
                ];
            }
        }
        return $this->commonReturn($res,'数据表中未定义字段，请添加后刷新重试',$res);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function makeModel(Request $request)
    {
        $params = $this->checkParams($request,[
            'type' => '功能选择不能为空',
            'modelName' => '模型名不能为空'
        ]);
        if(!is_array($params)) return $this->returnFail($params);
        try{
            Artisan::call("make:model {$params['modelName']} -m");
            $res = Artisan::output();
            if(strstr($res,'successfully')){
                return $this->returnSuccess('模型创建成功！');
            }else{
                return $this->returnFail($res);
            }
        }catch (\Exception $exception){
            return $this->returnFail($exception->getMessage());
        }
    }

    /**
     * @param $flag
     * @param $message
     * @param null $data
     * @return array
     */
    function commonReturn($flag, $message, $data = null)
    {
        if ($flag || $flag === '' || $flag === []) {
            return $this->returnSuccess($data);
        } else {
            return $this->returnFail($message);
        }
    }

    /**
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

    /**
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
