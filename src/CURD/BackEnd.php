<?php
namespace HXC\LaravelGenerate\CURD;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait backEnd
{
    protected $request;
    protected $repository;
    //列表查询要显示的字段
    protected $indexField = [];
    //单条数据查询要显示的字段
    protected $showField = [];
    //默认排序
    protected $orderBy = ['id' => 'desc'];
    //关联查询
    protected $with = [];
    //搜索相关，hxc为内容占位符，规范：
    //1、'接受的参数名/搜索的字段名' => ['逻辑','内容']
    //2、'接受的参数名/搜索的字段名' => ['逻辑','内容','关联查询定义的方法名']
    //3、'接受的参数名/搜索的字段名' => 'time' 时间查询
    protected $search = [];


    /**
     * 列表查询的条件，可以直接追加查询条件
     * @param $sql
     * @return mixed
     */
    protected function indexWhere($sql)
    {
        return $sql;
    }

    /**
     * 创建数据的处理，入库前
     * @param $params
     * @return mixed
     */
    protected function storeParams($params)
    {
        return $params;
    }

    /**
     * 更新数据的处理，入库前
     * @param $params
     * @param
     * @return mixed
     */
    protected function updateParams($params,$id)
    {
        return $params;
    }

    /**
     * 编辑数据查询结果处理，查询完成后，需自己判断是否为空结果
     * @param $data
     * @return mixed
     */
    protected function showResultData($data)
    {
        return $data;
    }

    /**
     * 列表数据查询结果处理，查询完成后，需自己判断是否为空结果
     * @param $data
     * @return mixed
     */
    protected function indexResultData($data)
    {
        return $data;
    }

    /**
     * 编辑数据后结果处理
     * @param $data
     * @param $id @desc 编辑的id
     * @return mixed
     */
    protected function updateResultData($data,$id)
    {
        return $data;
    }

    /**
     * 创建数据后结果处理
     * @param $data
     * @param $create_model @desc 创建后的对象
     * @return mixed
     */
    protected function storeResultData($data,$create_model)
    {
        return $data;
    }

    /**
     * 删除数据后结果处理
     * @param $id
     * @return mixed
     */
    protected function deleteResultData($id)
    {
        return '';
    }

    /**
     * @param $data
     * @return bool
     */
    protected function checkResultData($data)
    {
        if(isset($data['code']) && $data['code'] === 0 && isset($data['status']) && $data['status'] === 'fail'){
            return false;
        }
        return true;
    }


    /**
     * Display a listing of the resource.
     *
     * @param  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $params = $request->all();
        $res = $this->repository->pagination(function ($sql)use($params){
            $sql = $this->indexWhere($sql);
            if(is_array($this->orderBy) && count($this->orderBy) > 0){
                foreach ($this->orderBy as $key => $value){
                    $sql = $sql->orderBy($key,$value);
                }
            }
            if(is_array($this->search) && count($this->search) > 0){
                foreach ($this->search as $key => $value){
                    if(is_array($value)){
                        $value_count = count($value);
                        if($value_count == 2){
                            if(isset($params[$key])) {
                                $sql = $sql->where($key,$value[0],str_replace('hxc',$params[$key],$value[1]));
                            }
                        }elseif ($value_count == 3){
                            if(isset($params[$key])){
                                $sql = $sql->whereHas($value[2],function (Builder $query)use ($key,$value,$params){
                                    $query->where($key,$value[0],str_replace('hxc',$params[$key],$value[1]));
                                });
                            }
                        }
                    }elseif (is_string($value)){
                        if($value === 'time' && isset($params[$key]) && is_array($params[$key])) {
                            if($params[$key][0] && $params[$key][1]) $sql = $sql->where($key, '>=',$params[$key][0])->where($key, '<=',$params[$key][1]);
                        }
                    }
                }
            }
            if(is_array($this->with) && count($this->with) > 0){
                $sql = $sql->with($this->with);
            }
            return $sql;
        },$this->indexField);
        $index_data = $this->indexResultData($res);
        if(!$this->checkResultData($index_data)){
            return $index_data;
        }
        return commonReturn($index_data->toArray()['data'],'查询错误',$index_data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //验证接收的参数
        $params = $this->request->validated();
        //参数数据二次处理
        $params = $this->storeParams($params);
        //如果有错误，则抛出
        if(!$this->checkResultData($params)){
            return $params;
        }
        DB::beginTransaction();
        try{
            //执行数据创建
            $res = $this->repository->create($params);
            if($res){
                //执行数据创建后的钩子
                $store_res = $this->storeResultData($params,$res);
                //验证结果
                if(!$this->checkResultData($store_res)){
                    DB::rollBack();
                    return returnFail($store_res);
                }
                DB::commit();
                return returnSuccess();
            }else{
                DB::rollBack();
                return returnFail('创建失败');
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return returnFail($exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $res = $this->repository->find(function ($sql)use($id){
            if(is_array($this->with) && count($this->with) > 0){
                $sql = $sql->with($this->with);
            }
            return $sql->where('id',$id);
        },$this->showField);
        $res = $this->showResultData($res);
        if(!$this->checkResultData($res)){
            return $res;
        }
        return commonReturn($res,'查询失败',$res);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $params = $this->request->validated();
        $params = $this->updateParams($params,$id);
        if(!$this->checkResultData($params)){
            return $params;
        }
        DB::beginTransaction();
        try{
            //执行更新操作
            $res = $this->repository->update($id,$params);
            if($res){
                //更新后其他操作钩子
                $update_res = $this->updateResultData($params,$id);
                //验证结果
                if(!$this->checkResultData($update_res)){
                    DB::rollBack();
                    return returnFail($update_res);
                }
                DB::commit();
                return returnSuccess();
            }else{
                DB::rollBack();
                return returnFail('修改失败');
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return returnFail($exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete_data = $this->repository->find($id);
        if(!$delete_data){
            return returnFail('没有找到此数据');
        }
        DB::beginTransaction();
        try{
            $res = $delete_data->delete();
            if($res){
                $delete_res = $this->deleteResultData($delete_data,$id);
                //验证结果
                if(!$this->checkResultData($delete_res)){
                    DB::rollBack();
                    return returnFail($delete_res);
                }
                DB::commit();
            }else{
                DB::rollBack();
                return returnFail('删除失败');
            }
        }catch (\Exception $exception){
            DB::rollBack();
            return returnFail($exception->getMessage());
        }
    }
}
