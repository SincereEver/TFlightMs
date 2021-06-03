define(['jquery', 'bootstrap', 'backend', 'form', 'template', 'loading'], function($, undefined, Backend, Form, Template, Loading) {
    var Controller = {
        index: function() {
            Controller.api.events.toolbar();
            Controller.init.table();
        },
        add: function() {
            Controller.init.selectpicker();
            Controller.api.form();
        },
        edit: function() {
            Controller.init.selectpicker(true);
            Controller.api.form();
        },
        init: {
            selectpicker: function(disabled = false) {
                $('#sType').on('loaded.bs.select , changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
                    var id = $('#id').val();
                    var sType = $(this).selectpicker('val');
                    $('#sTypeDom').hide();
                    $('.rememory').hide();
                    $('.urladdress').hide();
                    $('.sbody').hide();
                    switch (sType) {
                        case 'toShell':
                            $('.sbody').show().find('textarea').attr('placeholder', '');
                            $('#sBodyTitle').html('脚本内容:');
                            break;
                        case 'site':
                            Controller.init.toBackup(sType);
                            $('.sbody').show().find('textarea').attr('placeholder', '每行一条规则,目录不能以/结尾，示例：\ndata/config.php\nstatic/upload\n *.log\n').text();
                            $('#sBodyTitle').html('排除规则:');
                            break;
                        case 'database':
                            Controller.init.toBackup(sType);
                            break;
                        case 'logs':
                            Controller.init.toBackup(sType);
                            break;
                        case 'path':
                            Controller.init.toBackup(sType);
                            $('.sbody').show().find('textarea').attr('placeholder', '每行一条规则,目录不能以/结尾，示例：\ndata/config.php\nstatic/upload\n *.log\n').text();
                            $('#sBodyTitle').html('排除规则:');
                            break;
                        case 'rememory':
                            $('.rememory').show();
                            break;
                        case 'toUrl':
                            $('.urladdress').show();
                            break;
                        case 'syncTime':
                            $(this).selectpicker('val', 'toShell');
                            $(this).selectpicker('refresh');
                            $('.sbody').show().find('textarea').text('echo "|-正在尝试从0.pool.bt.cn同步时间..";\n\
                            ntpdate -u 0.pool.bt.cn\n\
                            if [ $? = 1 ];then\n\
                                echo "|-正在尝试从1.pool.bt.cn同步时间..";\n\
                                ntpdate -u 1.pool.bt.cn\n\
                            fi\n\
                            if [ $? = 1 ];then\n\
                                echo "|-正在尝试从0.asia.pool.ntp.org同步时间..";\n\
                                ntpdate -u 0.asia.pool.ntp.org\n\
                            fi\n\
                            if [ $? = 1 ];then\n\
                                echo "|-正在尝试从www.bt.cn同步时间..";\n\
                                getBtTime=$(curl -sS --connect-timeout 3 -m 60 http://www.bt.cn/api/index/get_time)\n\
                                if [ "${getBtTime}" ];then	\n\
                                    date -s "$(date -d @$getBtTime +"%Y-%m-%d %H:%M:%S")"\n\
                                fi\n\
                            fi\n\
                            echo "|-正在尝试将当前系统时间写入硬件..";\n\
                            hwclock -w\n\
                            date\n\
                            echo "|-时间同步完成!";');
                            break;
                        default:
                           
                            break;
                    }
                    if (disabled) {
                        $(this).prop('disabled', true);
                        $(this).selectpicker('refresh');
                    }
                });
                $('#type').on('loaded.bs.select , changed.bs.select', function(e, clickedIndex, isSelected, previousValue) {
                    var type = $(this).selectpicker('val');
                    $('#whereDom').html(Template(type + "-tpl", {}));
                    if (type == 'week') $('#whereDom').find('.selectpicker').selectpicker('refresh');
                });
            },
            toBackup: function(sType = '') {
                $.ajax({
                    type: "POST",
                    url: "btpanel/ajax/getDataList",
                    data: { type: sType == 'database' ? 'databases' : 'sites' },
                    dataType: "JSON",
                    success: function(res) {
                        res.data.unshift({ name: '所有', ps: 'ALL' });
                        res.orderOpt.unshift({ name: '服务器磁盘', value: 'localhost' });
                        res.title = Config.crontab['sTypeArray'][sType];
                        var html;
                        if (sType == 'path') {
                            html = Template("sTypePathTpl", res);
                        } else {
                            html = Template("sTypeSiteTpl", res);
                        }
                        $('#sTypeDom').html(html);
                        $('#sTypeDom').show();
                        $('#sTypeDom').find('.selectpicker').selectpicker('refresh');
                    }
                });
            },
            table: function() {
                $('table').loading();
                $.ajax({
                    type: "POST",
                    url: "btpanel/ajax/getCrontab",
                    dataType: "JSON",
                    success: function(rdata) {
                        if (rdata == []) {
                            $('table').loading('hide');
                            var html = Template("<tr><td colspan='6'>暂无数据</td></tr>", {});
                            $('#table-crontab').html(html);
                        } else {
                            $.post('btpanel/ajax/getDataList', {}, function(res) {
                                for (var i = 0; i < rdata.length; i++) {
                                    for (var j = 0; j < res.orderOpt.length; j++) {
                                        if (rdata[i].backupTo == 'localhost') {
                                            rdata[i].backupTo = '本地磁盘';
                                        } else if (rdata[i].backupTo == res.orderOpt[j].value) {
                                            rdata[i].backupTo = res.orderOpt[j].name;
                                        } else if (rdata[i].backupTo == '') {
                                            rdata[i].backupTo = ''
                                        }
                                    }
                                    var arrs = ['site', 'database', 'path'];
                                    if ($.inArray(rdata[i].sType, arrs) == -1) rdata[i].backupTo = "--";
                                }
                                $('table').loading('hide');
                                var html = Template('crontabtpl', { data: rdata });
                                $('#table-crontab').html(html);
                                //绑定各种事件
                                Controller.api.events.startTask();
                                Controller.api.events.getLogs();
                                Controller.api.events.delCrontab();
                                Controller.api.events.getCrontabFind();
                                Controller.api.events.setTaskStatus();

                            });
                        }

                    }
                });
            },
        },
        api: {
            events: {
                delCrontab: function() {
                    $('.btn-del').click(function() {
                        var id = $(this).parent().data('id');
                        var title = $(this).parent().siblings('td[data-field="name"]').html();
                        var confirm = Layer.confirm('是否要删除【' + title + '】？', { title: '提示', icon: 3 }, function(index) {
                            if (index > 0) {
                                var loadT = Layer.msg('正在删除，请稍后...', { icon: 16, time: 0, shade: [0.3, '#000'] });
                                $.post('btpanel/ajax/delCrontab', { id: id }, function(rdata) {
                                    Layer.closeAll();
                                    Layer.close(confirm);
                                    Layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                                    if (rdata.status) Controller.init.table();
                                });
                            }
                        });
                    })
                },
                toolbar: function() {
                    $('.btn-refresh').click(function() {
                        Controller.init.table();
                    })
                    $('.btn-add').click(function() {
                        Fast.api.open('btpanel/crontab/add?', '新增定时任务', {
                            callback: function(data) {
                                Controller.init.table();
                            }
                        })
                    })
                },
                // 编辑计划任务
                getCrontabFind: function() {
                    $('.btn-edit').click(function() {
                        var id = $(this).parent().data('id');
                        var title = $(this).parent().siblings('td[data-field="name"]').html();
                        Fast.api.open('btpanel/crontab/edit?id=' + id, '【' + title + '】编辑', {
                            callback: function(data) {
                                Controller.init.table();
                            }
                        })
                    })
                },
                //执行任务脚本
                startTask: function() {
                    $('.btn-StartTask').click(function() {
                        var id = $(this).parent().data('id');
                        var title = $(this).parent().siblings('td[data-field="name"]').html();
                        var confirm = Layer.confirm('是否要立即执行【' + title + '】？', { title: '提示', icon: 3 }, function(index) {
                            if (index > 0) {
                                var loadT = Layer.msg('正在执行，请稍后...', { icon: 16, time: 0, shade: [0.3, '#000'] });
                                $.post('btpanel/ajax/startTask', { id: id }, function(rdata) {
                                    Layer.closeAll();
                                    Layer.close(confirm);
                                    Layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                                });
                            }
                        });
                    })
                },
                getLogs: function() {
                    $('.btn-GetLogs').click(function() {
                        $('table').loading();
                        var id = $(this).parent().data('id');
                        var title = $(this).parent().siblings('td[data-field="name"]').html();
                        $.post('btpanel/ajax/GetLogs', { id: id, type: 'crontab' }, function(rdata) {
                            $('table').loading('hide');
                            if (!rdata.status) {
                                Layer.msg(rdata.msg, { icon: 2 });
                                return;
                            };
                            Layer.open({
                                id: 'logs',
                                type: 1,
                                title: '【' + title + '】日志',
                                shadeClose: false,
                                btn: ['清空', '关闭'],
                                yes: function(index, layero) {
                                    var confirm = Layer.confirm('是否要清空【' + title + '】的日志？', { title: '提示', icon: 3 }, function(index) {
                                        if (index > 0) {
                                            var loadT = Layer.msg('正在执行，请稍后...', { icon: 16, time: 0, shade: [0.3, '#000'] });
                                            $.post('btpanel/ajax/delLogs', { id: id }, function(rdata) {
                                                Layer.closeAll();
                                                Layer.close(confirm);
                                                Layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                                            });
                                        }
                                    });
                                },
                                area: ['800px', '600px'], //宽高
                                content: '<pre id="editor" style="overflow: auto; border: 0px none; line-height:23px;padding: 15px; margin: 0px; white-space: pre-wrap; height: 558px; background-color: rgb(51,51,51);color:#f1f1f1;border-radius:0px;font-family: \"微软雅黑\"">' + (rdata.msg == '' ? '当前日志为空' : rdata.msg) + '</pre>',
                                success: function() {
                                    var scrollHeight = $('#editor').prop("scrollHeight");
                                    $('#editor').animate({ scrollTop: scrollHeight }, 200);
                                }

                            });
                        });
                    })
                },
                setTaskStatus: function() {
                    $('.set_task_status').off('click').on('click', function() {
                        var id = $(this).data('id');
                        var status = $(this).children('span').data('status');
                        var title = $(this).parent().siblings('td[data-field="name"]').html();
                        var confirm = Layer.confirm(status == '0' ? '计划任务暂停后将无法继续运行，您真的要停用【' + title + '】吗？' : '该计划任务已停用，是否要启用【' + title + '】', { title: '提示', icon: 3 }, function(index) {
                            if (index > 0) {
                                var loadT = Layer.msg('正在设置状态，请稍后...', { icon: 16, time: 0, shade: [0.3, '#000'] });
                                $.post('btpanel/ajax/setCronStatus', { id: id }, function(rdata) {
                                    Layer.closeAll();
                                    Layer.close(confirm);
                                    Layer.msg(rdata.msg, { icon: rdata.status ? 1 : 2 });
                                    if (rdata.status) Controller.init.table();
                                });
                            }
                        });
                    })
                }
            },

            form: function() {
                Form.api.bindevent($("form[role=form]"), null, null, function(success, error) {
                    var form = {
                        id: '',
                        name: '',
                        type: '',
                        where1: '',
                        hour: '',
                        minute: '',
                        week: '',
                        sType: '',
                        sBody: '',
                        sName: '',
                        backupTo: '',
                        save: '',
                        urladdress: '',
                    }
                    $('input[class="form-control"],textarea[class="form-control"],select[class="form-control selectpicker"]').each(function(i, element) {
                        element == this
                        var name = $(element).attr('name');
                        var value = $(element).val();
                        if (form.hasOwnProperty(name)) {
                            form[name] = value;
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: $(this).attr('action'),
                        data: form,
                        dataType: "JSON",
                        success: function(ret) {
                            var index = parent.Layer.getFrameIndex(window.name);
                            parent.Layer.close(index);
                            parent.Layer.msg(ret.msg, { icon: ret.status ? 1 : 2 });
                            Fast.api.close(ret)
                        }
                    });
                    return false;
                });
            }
        }
    };
    return Controller;
});