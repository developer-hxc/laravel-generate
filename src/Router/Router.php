<?php
/**
 * 生成工具路由
 */
Route::namespace('HXC\LaravelGenerate\controller')->prefix('hxc/generate')->group(function(){
    Route::get('/','generateController@index');
    Route::get('tables','generateController@getTables');
    Route::get('tables/fields','generateController@getTableFieldsData');
    Route::post('make/model','generateController@makeModel');
});

