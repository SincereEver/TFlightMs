define(['jquery', 'bootstrap', 'backend', 'form', 'table', 'echarts', 'template', 'knob','loading'], function($, undefined, Backend, Form, Table, Echarts, Template,Loading) {
    var network, isInit = true;
    var rate = 2000;
    var lineChart = [];
    var Controller = {
        index: function() {
            Controller.init.knob();
            Controller.init.table();
            $(document).loading(); 
            var height = $('.panel-ServerInfo .panel-body').height()-65;
            $('.panel-liveLine .tab-pane').height(height + 'px');
            /* shown.bs.tab为tab选项卡高亮 */
            $('#liveLine a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                lineChart[$(this).data('chart')].resize();
            });
            Controller.module.net.chart.init(height - $('#net-info').height()-10);
            Controller.module.cpu.chart.init(height);
            setInterval(function() {
                $.ajax({
                    type: "Post",
                    url: "btpanel/ajax/getNetWork",
                    dataType: "json",
                    success: function(res) {
                        network = res;
                        Controller.module.cpu.value(res.cpu);
                        Controller.module.load.value(res.load);
                        Controller.module.memory.value(res.mem);
                        Controller.module.disk.value(res.disk);
                        Controller.module.net.value(res.up, res.down, res.upTotal, res.downTotal);
                        if (isInit) {
                            isInit = false;
                            $(document).loading('hide');
                            Controller.init.event();
                        }
                    }
                });
            }, rate);
        },
        init: {
            table: function() {
                var table = $("#table");

                // 初始化表格
                table.bootstrapTable({
                    url: 'btpanel/ajax/getSiteData',
                    pk: 'id',
                    sort:'id',
                    pagination: false,
                    columns: [
                        [
                            { field: 'id', title: 'ID'},
                            { field: 'name', title: '网站名',formatter: Controller.api.table.formatter.name},
                            { field: 'path', title: '根目录' },
                            { field: 'backup_count', title: '备份' },
                            { field: 'addtime', title: '创建时间' },
                            { field: 'edate', title: '到期时间' },
                            { field: 'status', title: '状态', formatter: Controller.api.table.formatter.status },
                            { field: 'ps', title: '备注' }
                        ]
                    ]
                });

                $('#siteType').change(function (e) { 
                    e.preventDefault();
                    table.bootstrapTable('refresh',{
                        url: 'btpanel/index/getSiteData?type='+$('#siteType option:selected').val()
                    });
                });
                // 为表格绑定事件
                Table.api.bindevent(table);
            },
            knob: function() {
                $(".knob").knob({
                    'fgColor': "#3c8dbc",
                    'readOnly': true,
                    'step': 0.1,
                    'height': '100px',
                    format: function(v) {
                        return v + '%';
                    },
                    release: function(v) {
                        $('.knob').attr('type', 'text');
                    }
                });
            },
            event: function() {
                Controller.module.cpu.hover();
                Controller.module.load.hover();
                Controller.module.memory.hover();
                Controller.module.disk.hover();
            }
        },
        module: {
            cpu: {
                value: function(data) {
                    $('.knob-cpu').val(data[0]).trigger('change');
                    Controller.api.echarts.addData(lineChart['cpu'], [data[0]]);
                },
                hover: function(data) {
                    $('#panel-cpu').hover(function() {
                        var d = network.cpu;
                        if (d) {
                            var _this = $(this);
                            var crs = '';
                            var n1 = 0;
                            for (var i = 0; i < d[2].length; i++) {
                                n1++;
                                crs += 'CPU-' + i + ": " + d[2][i] + '%' + (n1 % 2 == 0 ? '</br>' : ' | ');
                            }
                            layer.tips(d[3] + "</br>" + d[5] + "个物理CPU," + (d[5] * d[4]) + "个物理核心," + d[1] + "线程</br>" + crs, _this.find('.knob-cpu'), { time: 0, tips: [3, '#999'] });
                        }
                    }, function() {
                        layer.closeAll('tips');
                    });
                },
                chart: {
                    init: function(height) {
                        // 基于准备好的dom，初始化echarts实例
                        $('#chart-cpu').height(height)
                        var Chart = Echarts.init(document.getElementById('chart-cpu'), 'walden');
                        var obj = {};
                        obj.dataZoom = [];
                        obj.unit = '百分比';
                        obj.dataset = {
                            dimensions: ['时间', 'CPU'],
                            source: []
                        };
                        obj.series = [{
                            type: 'line',
                            smooth: true,
                            symbol: 'none',
                            lineStyle: { normal: { width: 1, color: '#aaa' } },
                            itemStyle: { normal: { color: '#52a9ff' } },
                            areaStyle: { opacity: 0.5 }
                        }];
                        var option = Controller.api.echarts.getOptions(obj);

                        // 使用刚指定的配置项和数据显示图表。
                        Chart.setOption(option);
                        $(window).resize(function() {
                            Chart.resize();
                        });
                        lineChart['cpu'] = Chart;
                        return Chart;
                    },

                }

            },
            load: {
                value: function(data) {
                    var _lval = Math.round((data.one / data.max) * 100);
                    if (_lval > 100) _lval = 100;
                    $('.knob-load').val(_lval).trigger('change');
                },
                hover: function() {
                    $('#panel-load').hover(function() {
                        var _this = $(this);
                        var d = network.load;
                        layer.tips('最近1分钟平均负载：' + d.one + '</br>最近5分钟平均负载：' + d.five + '</br>最近15分钟平均负载：' + d.fifteen + '', _this.find('.knob-load'), { time: 0, tips: [3, '#999'] });
                    }, function() {
                        layer.closeAll('tips');
                    })
                }
            },
            memory: {
                value: function(data) {
                    var _memory = (data.memRealUsed * 100 / data.memTotal).toFixed(1)
                    $('.knob-memory').val(_memory).trigger('change');
                },
                hover: function() {
                    $('#panel-memory').hover(function() {
                        var _this = $(this);
                        var d = network.mem;
                        var tips = '总大小:' + d.memTotal + '<br>' +
                            '可用的物理内存: ' + d.memFree + ' MB<br>' +
                            '已使用的物理内存: ' + d.memRealUsed + ' MB<br>' +
                            '缓存化内存: ' + d.memCached + ' MB<br>' +
                            '系统缓冲: ' + d.memBuffers + ' MB';
                        layer.tips(tips, _this.find('.knob-memory'), { time: 0, tips: [3, '#999'] });
                    }, function() {
                        layer.closeAll('tips');
                    })
                }
            },
            disk: {
                value: function(data) {
                    if (data.length > 0) {
                        var size = data[0].size[3];
                        $('.knob-disk').val(size.replace('%', '')).trigger('change');
                    }
                },
                hover: function() {
                    $('#panel-disk').hover(function() {
                        var _this = $(this);
                        var d = network.disk;
                        var tips = '';
                        $.each(d, function(index, item) {
                            tips += '<b>基础信息</b><br>' +
                                '文件系统:' + item.filesystem + '<br>' +
                                '类型:' + item.type + '<br>' +
                                '挂载点:' + item.path + '<br>' +
                                '<b>Inode信息</b><br>' +
                                '总数:' + item.inodes[0] + '<br>' +
                                '已用:' + item.inodes[1] + '<br>' +
                                '可用:' + item.inodes[2] + '<br>' +
                                'Inode使用率:' + item.inodes[3] + '<br>' +
                                '<b>容量信息</b><br>' +
                                '容量:' + item.size[0] + '<br>' +
                                '已用:' + item.size[1] + '<br>' +
                                '可用:' + item.size[2] + '<br>' +
                                '使用率:' + item.size[3];
                        });
                        layer.tips(tips, _this.find('.knob-disk'), { time: 0, tips: [3, '#999'] });
                    }, function() {
                        layer.closeAll('tips');
                    })
                }
            },
            net: {
                value: function(up, down, upTotal, downTotal) {
                    $('#upSpeed').html(up + ' KB');
                    $('#downSpeed').html(down + ' KB');
                    $('#upAll').html(Controller.api.formatBytes(upTotal, 2));
                    $('#downAll').html(Controller.api.formatBytes(downTotal, 2));
                    Controller.api.echarts.addData(lineChart['net'], [up, down]);
                },
                chart: {
                    init: function(height) {
                        // 基于准备好的dom，初始化echarts实例
                        $('#chart-net').height(height)
                        var Chart = Echarts.init(document.getElementById('chart-net'), 'walden');
                        var obj = {};
                        obj.dataZoom = [];
                        obj.unit = '单位:KB/s';
                        obj.dataset = {
                            dimensions: ['时间', '上行', '下行'],
                            source: []
                        };
                        obj.series = [{
                            type: 'line',
                            smooth: true,
                            symbol: 'none',
                            lineStyle: { normal: { width: 1, color: '#aaa' } },
                            itemStyle: { normal: { color: '#f7b851' } },
                            areaStyle: { opacity: 0.5 }
                        }, {
                            type: 'line',
                            smooth: true,
                            symbol: 'none',
                            lineStyle: { normal: { width: 1, color: '#aaa' } },
                            itemStyle: { normal: { color: '#52a9ff' } },
                            areaStyle: { opacity: 0.5 }
                        }];
                        var option = Controller.api.echarts.getOptions(obj);

                        // 使用刚指定的配置项和数据显示图表。
                        Chart.setOption(option);
                        $(window).resize(function() {
                            Chart.resize();
                        });
                        lineChart['net'] = Chart;
                        return Chart;
                    },

                }
            }
        },
        api: {
            echarts: {
                getOptions(obj) {
                    var option = {
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'cross'
                            },
                            formatter: obj.formatter
                        },
                        dataset: obj.dataset,
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            axisLine: {
                                lineStyle: {
                                    color: "#666"
                                }
                            }
                        },
                        grid: {
                            left: '5%',
                            right: '5%',
                            bottom: '10%',
                            top: '10%'
                        },
                        yAxis: {
                            type: 'value',
                            name: obj.unit,
                            boundaryGap: [0, '100%'],
                            min: 0,
                            splitLine: {
                                lineStyle: {
                                    color: "#ddd"
                                }
                            },
                            axisLine: {
                                lineStyle: {
                                    color: "#666"
                                }
                            }
                        },
                        dataZoom: [{
                            type: 'inside',
                            start: 0,
                            zoomLock: true
                        }, {
                            start: 0,
                            handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                            handleSize: '80%',
                            handleStyle: {
                                color: '#fff',
                                shadowBlur: 3,
                                shadowColor: 'rgba(0, 0, 0, 0.6)',
                                shadowOffsetX: 2,
                                shadowOffsetY: 2
                            }
                        }],
                        series: []
                    };
                    if (obj.legend) option.legend = obj.legend;
                    if (obj.dataZoom) option.dataZoom = obj.dataZoom;

                    for (var i = 0; i < obj.series.length; i++) {
                        var item = obj.series[i];
                        var series = {
                            type: item.type ? item.type : 'line',
                            smooth: item.smooth ? item.smooth : true,
                            symbol: item.symbol ? item.symbol : 'none',
                            showSymbol: item.showSymbol ? item.showSymbol : false,
                            sampling: item.sampling ? item.sampling : 'average',
                            areaStyle: item.areaStyle ? item.areaStyle : {},
                            lineStyle: item.lineStyle ? item.lineStyle : {},
                            itemStyle: item.itemStyle ? item.itemStyle : { normal: { color: 'rgb(0, 153, 238)' } },
                            symbolSize: 6,
                            symbol: 'none'
                        }
                        option.series.push(series);
                    }
                    return option;
                },
                addData: function(chart, arr, limit = 300) {
                    var option = chart.getOption();
                    var data = option.dataset[0].source;
                    var now = new Date();
                    var time = [now.getHours(), now.getMinutes() + 1, now.getSeconds()].join(':');
                    var newData = [time];
                    newData = newData.concat(arr)
                    data.push(newData);
                    if (limit && data.length > limit / (rate / 1000)) {
                        data.shift();
                    }
                    chart.setOption({
                        dataset: {
                            source: data
                        }
                    });
                }
            },
            table: {
                formatter: {
                    name: function(value, row, index) {
                        $.ajax({
                            type: "POST",
                            url: "btpanel/ajax/getWebSiteDomain",
                            data: {
                                id:row.id
                            },
                            async: false,
                            dataType: "json",
                            success: function (res) {
                                $.each(res, function (i, domain) { 
                                     if(document.domain == domain.name){
                                        value =  domain.name+' <span class="label label-success">当前站点</span>';
                                     }
                                });
                            }
                        });
                        return value
                    },
                    status: function(value, row, index) {
                        var color = value == '1' ? 'text-green' : 'text-red';
                        var icon = value == '1' ? 'fa-play' : 'fa-pause';
                        var text = value == '1' ? '运行中' : '已停止';
                        return "<span class="+color+">"+text+"<i class='fa fa-fw " + icon + "'></i></span>"
                    }
                }
            },
            formatBytes: function(a, b) {
                if (0 == a) return "0 Bytes";
                var c = 1024,
                    d = b || 2,
                    e = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
                    f = Math.floor(Math.log(a) / Math.log(c));
                return parseFloat((a / Math.pow(c, f)).toFixed(d)) + " " + e[f]
            },
            bindevent: function() {
                Form.api.bindevent($("form[role=form]"));
            },
        }
    };
    return Controller;
});