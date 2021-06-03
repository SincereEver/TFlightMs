define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'developer_account/index' + location.search,
                    add_url: 'developer_account/add',
                    edit_url: 'developer_account/edit',
                    del_url: 'developer_account/del',
                    multi_url: 'developer_account/multi',
                    import_url: 'developer_account/import',
                    table: 'developer_account',
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
                        {field: 'appid', title: __('Appid'), operate: 'LIKE'},
                        {field: 'team_name', title: __('团队名称'), operate: 'LIKE'},
                        {field: 'lssuer_id', title: __('Lssuer_id'), operate: 'LIKE'},
                        {field: 'key_id', title: __('Key_id'), operate: 'LIKE'},
                        //{field: 'p8_file', title: __('P8_file'), operate: false},
                        //{field: 'certificate_file', title: __('Certificate_file'), operate: false},
                        //team_name
                        
                        //{field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        //{field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'login_human_date', title: __('Logintime'), operate:false},
                        {field: 'status_switch', title: __('Status_switch'), table: table, formatter: Table.api.formatter.toggle},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                        buttons:[
                            {
                                name:'loginStatus',
                                title:'刷新登录会话',
                                classname:'btn btn-xs btn-primary btn-dialog',
                                icon:'fa fa-repeat',
                                url:'developer_account/repeat',
                                extend: 'data-area=\'["400px", "220px"]\' data-shade=\'0.5\'',
                                callback: function (data) {
                                    $("a.btn-refresh").trigger("click");
                                }
                            }
                        ]
                    }
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
            }
        }
    };
    return Controller;
});