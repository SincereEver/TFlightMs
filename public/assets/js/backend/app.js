define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'app/index' + location.search,
                    add_url: 'app/add',
                    edit_url: 'app/edit',
                    del_url: 'app/del',
                    multi_url: 'app/multi',
                    import_url: 'app/import',
                    table: 'app',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                rowStyle:(row,index)=>{
                    if(row.is_check==0){
                        return {css:{"color":"#ccc"}}
                    }else{
                        return {};
                    }
                },
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'is_check',
                fixedColumns:true,
                fixedRightNumber:1,
                fixedNumber:4,
                columns: [
                    [{
                            checkbox: true
                        },
                        {
                            field: 'id',
                            title: __('Id')
                        },
                        {
                            field: 'is_check',
                            title: __('监控'),
                            table: table,
                            formatter: Table.api.formatter.toggle
                        },
                        {
                            field: 'icon_image',
                            title: __('Icon_image'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },

                        {
                            field: 'name',
                            title: __('Name'),
                            operate: 'LIKE',
                        },

                        {
                            field: 'bid',
                            title: __('Bid'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'down_url',
                            title: __('下载链接'),
                            operate: false,
                            formatter: this.formatter.urls,
                            events: this.events.copy
                        },
                        {
                            field: 'qr_link',
                            title: __('二维码'),
                            operate: false,
                            events: Table.api.events.image,
                            formatter: Table.api.formatter.image
                        },
                        {
                            field: 'links_count',
                            title: __('可用链接'),
                            formatter:this.formatter.linksCount
                        },
                        {
                            field: 'username',
                            title: __('所属用户'),
                            // formatter:(v)=>{
                            //     return '<span class="label label-success">'+v+'</span>';
                                
                            // }
                        },
                        {
                            field: 'dev_id',
                            title: __('Developer_account_id'),
                            operate: 'LIKE',
                            // formatter:(v)=>{
                            //     return '<span class="label label-primary">'+v+'</span>';
                                
                            // }
                        },
                        {
                            field: 'download_count',
                            title: __('Download_count')
                        },
                        {
                            field: 'view_count',
                            title: __('View_count')
                        },
                        
                        
                        
                        // {
                        //     field: 'developer_account_id',
                        //     title: __('Developer_account_id'),
                        //     operate: 'LIKE'
                        // },
                        // {
                        //     field: 'user_id',
                        //     title: __('User_id')
                        // },
                        
                        {
                            field: 'remarks',
                            title: __('Remarks'),
                            operate: 'LIKE'
                        },
                        {
                            field: 'status_switch',
                            title: __('Status_switch'),
                            table: table,
                            formatter: Table.api.formatter.toggle
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            //fa-users
                            buttons: [
                                {
                                    name:'tfLinks',
                                    classname:'btn btn-xs btn-primary btn-dialog',
                                    icon:'fa fa-link',
                                    title:'TF内测链接',
                                    url:'app/tfLinks',
                                },
                                {
                                    name:'geticon',
                                    classname:'btn btn-xs btn-primary btn-ajax',
                                    icon:'fa fa-file-image-o',
                                    title:'同步应用图标',
                                    url:'app/syncIcon',
                                    refresh:true
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
            },

        },
        formatter: { //渲染的方法
            linksCount:function (value, row, index) {
                if(value){
                    return `<a href="javascript:;" class="label label-primary btn-dialog" data-url="app/tfLinks/ids/${row.id}">${value}</a>`;

                }else{
                    return '<span style="color:#ccc">暂无<span>'
                }
               
            },
            azurls: function (value, row, index) {
                if(row.az_links){
                    return `<div class="input-group input-group-sm" ><span class="input-group-btn input-group-sm"><a title="打开地址" href="${value}" target="_blank" class="btn btn-default btn-copy btn-sm"><i  class="fa fa-link"></i> 查看</a></span></div>`;

                }else{
                    return '<span style="color:#ccc">暂无<span>'
                }
               
            },
            urls: function (value, row, index) {
                return `<div class="input-group input-group-sm" style="width:250px;"><input type="text" id="id${row.id}" class="form-control input-sm btn-copy" value="${value}"><span class="input-group-btn input-group-sm"><a title="打开地址" href="${value}" target="_blank" class="btn btn-default btn-copy btn-sm"><i  class="fa fa-link"></i></a></span></div>`;
            },
            ip: function (value, row, index) {
                return '<a class="btn btn-xs btn-ip bg-success"><i class="fa fa-map-marker"></i> ' + value + '</a>';
            }
        },
        events: {
            copy: {
                'click .btn-copy': function (e, value, row, index) {

                    var Url2 = document.getElementById("id" + row.id);

                    Url2.select(); // 选择对象
                    document.execCommand("Copy"); // 执行浏览器复制命令

                    Toastr.success("下载地址复制成功");
                }
            },
            copy2: {
                'click .btn-copy2': function (e, value, row, index) {

                    var Url2 = document.getElementById("2id" + row.id);

                    Url2.select(); // 选择对象
                    document.execCommand("Copy"); // 执行浏览器复制命令

                    Toastr.success("下载地址复制成功");
                }
            }
        }
    };
    return Controller;
});