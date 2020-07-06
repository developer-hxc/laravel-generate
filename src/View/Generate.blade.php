<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>桥通天下代码生成工具</title>
    <!-- import Vue.js -->
    <script src="//vuejs.org/js/vue.min.js"></script>
    <!-- import stylesheet -->
    <link rel="stylesheet" href="//unpkg.com/view-design/dist/styles/iview.css">
    <!-- import iView -->
    <script src="//unpkg.com/view-design/dist/iview.min.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <style>
        .layout .header,.layout .footer{
            height: 50px;
            line-height: 50px;
            text-align: center;
            background-color: #2d8cf0;
            color: #fff;
        }
        .layout .footer{
            background-color: #f8f8f9;
            color: #808695;
        }
        .layout .content{
            overflow: auto;
            background-color: #fff;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div id="app">
        <div class="layout">
            <Layout>
                <Header class="header" ref="header">桥通天下代码生成工具</Header>
                <Content class="content" ref="content">
                    <Tabs value="name1">
                        <tab-pane label="模型与迁移" name="name1">
                            <Alert show-icon>
                                <p>需要先创建<a href="https://learnku.com/docs/laravel/7.x/eloquent/7499" target="_blank">模型</a>和<a href="https://learnku.com/docs/laravel/7.x/migrations/7496" target="_blank">数据库的迁移</a>文件</p>
                                <p>模型文件路径：app/*.php</p>
                                <p>数据库迁移文件路径：database/migrations/*.php</p>
                            </Alert>
                            <Divider orientation="left">操作</Divider>
                            <i-form ref="mamForm" :rules="mamFormValidate" :model="mamForm" label-position="right" :label-width="80" style="width: 500px" >
                                <form-item label="功能选择">
                                    <radio-group v-model="mamForm.type" type="button" size="small">
                                        <Radio label="创建"></Radio>
                                        <Radio label="修改"></Radio>
                                    </radio-group>
                                </form-item>
                                <form-item label="模型名" prop="modelName" v-if="mamForm.type === '创建'">
                                    <p>
                                        <i-input v-model="mamForm.modelName" placeholder="数据表英文名的单数" size="small" style="width: 200px"/>
                                    </p>
                                    <p>
                                        <span style="color: #bdbdbd">* 生成数据表名为模型的名的复数，此过程自动且默认</span>
                                    </p>
                                </form-item>
                                <form-item>
                                    <i-button type="primary" @click="handleSubmit('mamForm')">确认</i-button>
                                </form-item>
                            </i-form>
                        </tab-pane>
                        <tab-pane label="代码生成" name="name2">
                            <div>
                                <Divider orientation="left">操作</Divider>
                                <i-form :model="form" label-position="right" :label-width="80" style="width: 300px">
                                    <form-item label="数据表">
                                        <i-select size="small" v-model="form.table" filterable placeholder="请选择数据表" @on-change="selectChange" clearable>
                                            <i-option v-for="item in tablesList" :value="item.value" :key="item.value">@{{ item.label }}</i-option>
                                        </i-select>
                                    </form-item>
                                </i-form>
                                <Divider orientation="left">数据表格</Divider>
                                <i-table :columns="tableColumns" :data="tableFieldsList" border no-data-text="暂无数据" :loading="loading"></i-table>
                            </div>
                        </tab-pane>
                        <tab-pane label="关联关系" name="name3">标签三的内容</tab-pane>
                    </Tabs>
                </Content>
                <Footer class="footer" ref="footer">桥通天下·潍坊</Footer>
            </Layout>
        </div>
    </div>
    <script>
        new Vue({
            el: '#app',
            data(){
                return {
                    mamForm:{
                        type:'创建',
                        modelName:''
                    },//模型与迁移的表单
                    form:{},
                    loading: false,//表格加载状态
                    tablesList: [],//数据表名列表
                    tableFieldsList:[],//数据表字段列表
                    mamFormValidate: {
                        modelName: [
                            { required: true, message: '模型名不能为空', trigger: 'blur' }
                        ],
                    },//模型与迁移的表单验证
                    tableColumns: [
                        {
                            title: '字段',
                            key: 'name',
                            resizable: true,
                            width: 180
                        },
                        {
                            title: '注释',
                            key: 'comment',
                            resizable: true,
                            width: 180
                        },
                        {
                            title: '类型',
                            key: 'type',
                            resizable: true,
                            width: 180
                        },
                        {
                            title: 'Address',
                            key: 'address'
                        }
                    ],

                }
            },
            mounted(){
                this.onresize()
                var that = this
                window.onresize = () => {
                    return (() => {
                        that.onresize()
                    })()
                }
            },
            created(){
                this.getTablesList()
            },
            methods:{
                onresize(){
                    this.$refs.content.style.height = (window.innerHeight - 100)+'px'
                },
                getTablesList(){
                    axios.get('/hxc/generate/tables').then(
                        res => {
                            if(res.data.code === 1){
                                this.tablesList = res.data.data
                            }else{
                                this.$Message.error(res.data.message)
                            }
                        }
                    )
                },
                selectChange(table){
                    this.loading = true
                    if(table){
                        axios.get('/hxc/generate/tables/fields?table='+table).then(
                            res => {
                                if(res.data.code === 1){
                                    this.tableFieldsList = res.data.data
                                }else{
                                    this.$Message.error(res.data.message)
                                }
                                this.loading = false
                            }
                        )
                    }else{
                        this.tableFieldsList = []
                        this.loading = false
                    }
                },
                handleSubmit (name) {
                    this.$refs[name].validate((valid) => {
                        if (valid) {
                            this.$Message.success('Success!');
                        } else {
                            this.$Message.error('Fail!');
                        }
                    })
                },
            },
            handleReset (name) {
                this.$refs[name].resetFields();
            }
        })
    </script>
</body>
</html>
