define(['jquery', 'bootstrap', 'backend', 'template'], function($, undefined, Backend, Template) {
    var Controller = {
        index: function() {
            window.navPages = function(page){
                Controller.init.table(page);
            }; 
            Controller.init.table(1);  
            Controller.api.events.panelErrorLogs();      
        },
        init:{
            table: function(page) {
                $.ajax({
                    type: "POST",
                    url: "btpanel/ajax/getLogs",
                    data: {
                        limit:20,
                        p:page,
                        tojs:$('#table-page').data('events')
                    },
                    dataType: "JSON",
                    success: function (res) {
                        var html = Template('logstpl', res);
                        $('#table-logs').html(html);
                        $('#table-page').html(res.page);
                    }
                });
            },
        },
        api: {
            events:{
                panelErrorLogs:function(){
                    $('.label-run-logs').click(function(){
                        $.ajax({
                            type: "POST",
                            url: "btpanel/ajax/getPanelErrorLogs",
                            dataType: "JSON",
                            success: function (res) {
                                Layer.open({
                                    type: 1,
                                    title:false,
                                    area: ['800px', '600px'], //宽高
                                    content: '<pre id="editor" style="overflow: auto; border: 0px none; line-height:23px;padding: 15px; margin: 0px; white-space: pre-wrap; height: 600px; background-color: rgb(51,51,51);color:#f1f1f1;border-radius:0px;font-family: \"微软雅黑\"">' + (res.msg == '' ? '当前日志为空' : res.msg) + '</pre>'
                                });
                            }
                        });
                        
                    });
                }
            }
        }
    };
    return Controller;
});