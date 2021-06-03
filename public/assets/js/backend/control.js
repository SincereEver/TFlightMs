define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'control/index' + location.search,
                    add_url: 'control/add',
                    edit_url: 'control/edit',
                    del_url: 'control/del',
                    multi_url: 'control/multi',
                    import_url: 'control/import',
                    table: 'control',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'bundleid', title: __('Bundleid'), operate: 'LIKE'},
                        {field: 'expire_datetime',sortable: true, title: __('Expire_datetime'), operate:'RANGE', formatter: this.api.s1},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('状态'), operate:false, formatter: this.api.s},
                        {field: 'install_count', sortable: true,title: __('Install_count')},
                        {field: 'run_count',sortable: true, title: __('Run_count')},
                        {field: 'max_install_count', title: __('Max_install_count')},
                        {field: 'remarks', title: __('Remarks'), operate: 'LIKE'},
                        
                        {field: 'expire_remark', title: __('Expire_remark'), operate: 'LIKE'},
                        {field: 'expire_type', title: __('Expire_type'), searchList: {"1":__('Expire_type 1'),"2":__('Expire_type 2'),"3":__('Expire_type 3')}, formatter: Table.api.formatter.label },
                        
                        
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        
                        //{field: 'expire_datetime', title: __('Expire_datetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate',
                        buttons:[
                            {
                                    name:'reset',
                                    classname:'btn btn-xs  btn-magic btn-ajax ',
                                    confirm: '将重置与应用相关的统计数据，是否继续？',
                                    url: 'control/reset',
                                    title:'掉签处理',
                                    text:'掉签处理',
                                    success: function (data, ret) {
                                        $(".btn-refresh").trigger("click");
                                        //Layer.alert(ret.msg + ",返回数据：" + JSON.stringify(data));
                                       
                                        //如果需要阻止成功提示，则必须使用return false;
                                        //return false;
                                    }
                             },
                            ], title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            },
            s1:function(v,row){
                var timestamp = (new Date()).getTime()/1000;
                console.log(timestamp)
                if(row.expire_datetime<timestamp){
                   return` <span style="color:#E74C3C">${row.expire_datetime_text}</span>`
                }else{
                    var ssa = row.expire_datetime-(3600*24*7);
                    if(ssa<timestamp){
                        return` <span style="color:#F39C12">${row.expire_datetime_text}</span>` 
                    }else{
                        return` <span class="text-success">${row.expire_datetime_text}</span>` 
                    }
                   
                }
                
            },
            s:function(v,row){
                var timestamp = (new Date()).getTime()/1000;
                console.log(timestamp)
                if(row.expire_datetime<timestamp){
                   return` <span style="color:#E74C3C"><i class="fa fa-circle"></i> 签名到期</span>`
                }else{
                    var ssa = row.expire_datetime-(3600*24*7);
                    if(ssa<timestamp){
                        return` <span style="color:#F39C12"><i class="fa fa-circle"></i> 即将到期</span>` 
                    }else{
                        return` <span class="text-success"><i class="fa fa-circle"></i> 签名正常</span>` 
                    }
                   
                }
                
            }
        }
    };
    return Controller;
});