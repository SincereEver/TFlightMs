define(['jquery', 'bootstrap', 'backend', 'echarts', 'template'], function($, undefined, Backend, Echarts, Template) {
    var chart = [];
    var Controller = {
        index: function() {
            if (!Config.SetControl.status) {
                Layer.confirm('检测到宝塔面板没有开启监控，是否立即开启?', {
                    icon: 3,
                    title: '提示',
                    btn: ['立即开启', '暂不开启'] //可以无限个按钮
                }, function(index, layero) {
                    Layer.close(index);
                    Layer.prompt({
                        value: Config.SetControl.day,
                        title: '保存天数'
                      }, function(value, index, elem){
                        $.ajax({
                            type: "POST",
                            url: "btpanel/ajax/setControl",
                            data: {
                                type:1, 
                                day:value
                            },
                            dataType: "JSON",
                            success: function(res) {
                                Layer.msg(res.msg)
                                Layer.close(index);
                            }
                        });
                      });
                });
            }
            //默认查询时间为今天
            $('.chart-line').data('start', Moment().startOf('day').unix());
            $('.chart-line').data('end', Moment().endOf('day').unix());
            Controller.api.echarts.init();
            Controller.api.daterangepicker();
        },
        module: {
            cpu: {
                init: function(data) {
                    var Chart = Echarts.init(document.getElementById('chart-cpu'), 'walden');
                    var obj = {};
                    obj.unit = '百分比';
                    obj.dataset = {
                        dimensions: ['addtime', 'pro'],
                        source: data
                    };
                    obj.formatter = function(params) {
                        return params[0].value.addtime + '<br>CPU:' + params[0].value.pro + '%';
                    }
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
                    chart['chart-cpu'] = Chart;
                    return Chart;
                }
            },
            load: {
                init: function(data) {
                    var Chart = Echarts.init(document.getElementById('chart-load'), 'walden');
                    var option = {
                            animation: false,
                            tooltip: {
                                trigger: 'axis',
                                axisPointer: {
                                    type: 'cross'
                                },
                                formatter: function(params) {
                                    return params[0].value.addtime + '<br>1分钟:' + params[0].value.one + '<br>5分钟:' + params[0].value.five + '<br>15分钟:' + params[0].value.fifteen + '<br>资源使用率:' + params[0].value.pro + '%';
                                }
                            },
                            legend: {
                                data: ['one', 'five', 'fifteen'],
                                right: '16%',
                                top: '10px',
                                formatter: function(name) {
                                    switch (name) {
                                        case 'one':
                                            name = '1分钟'
                                            break;
                                        case 'five':
                                            name = '5分钟'
                                            break;
                                        case 'fifteen':
                                            name = '15分钟'
                                            break;
                                    }
                                    return name;
                                }
                            },
                            axisPointer: {
                                link: { xAxisIndex: 'all' },
                                lineStyle: {
                                    color: '#aaaa',
                                    width: 1
                                }
                            },
                            dataset: [{
                                dimensions: ['addtime', 'pro', 'one', 'five', 'fifteen'],
                                source: data
                            }],
                            grid: [{ // 直角坐标系内绘图网格
                                    top: '10%',
                                    left: '2%',
                                    right: '55%',
                                    width: '45%',
                                    height: 'auto'
                                },
                                {
                                    top: '10%',
                                    left: '54%',
                                    width: '44%',
                                    height: 'auto'
                                }
                            ],
                            xAxis: [{ // 直角坐标系grid的x轴
                                    type: 'category',
                                    axisLine: {
                                        lineStyle: {
                                            color: '#666'
                                        }
                                    },
                                },
                                { // 直角坐标系grid的x轴
                                    type: 'category',
                                    gridIndex: 1,
                                    axisLine: {
                                        lineStyle: {
                                            color: '#666'
                                        }
                                    },
                                },
                            ],
                            yAxis: [{
                                    scale: true,
                                    name: '资源使用率%',
                                    splitLine: { // y轴网格显示
                                        show: true,
                                        lineStyle: {
                                            color: "#ddd"
                                        }
                                    },
                                    nameTextStyle: { // 坐标轴名样式
                                        color: '#666',
                                        fontSize: 12,
                                        align: 'left'
                                    },
                                    axisLine: {
                                        lineStyle: {
                                            color: '#666',
                                        }
                                    }
                                },
                                {
                                    scale: true,
                                    name: '负载详情',
                                    gridIndex: 1,
                                    splitLine: { // y轴网格显示
                                        show: true,
                                        lineStyle: {
                                            color: "#ddd"
                                        }
                                    },
                                    nameTextStyle: { // 坐标轴名样式
                                        color: '#666',
                                        fontSize: 12,
                                        align: 'left'
                                    },
                                    axisLine: {
                                        lineStyle: {
                                            color: '#666',
                                        }
                                    }
                                },
                            ],
                            dataZoom: [{
                                type: 'inside',
                                start: 0,
                                end: 100,
                                xAxisIndex: [0, 1],
                                zoomLock: true
                            }, {
                                xAxisIndex: [0, 1],
                                type: 'slider',
                                start: 0,
                                end: 100,
                                handleIcon: 'M10.7,11.9v-1.3H9.3v1.3c-4.9,0.3-8.8,4.4-8.8,9.4c0,5,3.9,9.1,8.8,9.4v1.3h1.3v-1.3c4.9-0.3,8.8-4.4,8.8-9.4C19.5,16.3,15.6,12.2,10.7,11.9z M13.3,24.4H6.7V23h6.6V24.4z M13.3,19.6H6.7v-1.4h6.6V19.6z',
                                handleSize: '100%',
                                handleStyle: {
                                    color: '#fff',
                                    shadowBlur: 3,
                                    shadowColor: 'rgba(0, 0, 0, 0.6)',
                                    shadowOffsetX: 2,
                                    shadowOffsetY: 2
                                },
                                left: '2%',
                                right: '2%'
                            }],
                            series: [{
                                    name: 'pro',
                                    type: 'line',
                                    lineStyle: {
                                        normal: {
                                            width: 2,
                                            color: 'rgb(255, 140, 0)'
                                        }
                                    },
                                    itemStyle: {
                                        normal: {
                                            color: 'rgb(255, 140, 0)'
                                        }
                                    },
                                },
                                {
                                    xAxisIndex: 1,
                                    yAxisIndex: 1,
                                    name: 'one',
                                    type: 'line',
                                    lineStyle: {
                                        normal: {
                                            width: 2,
                                            color: 'rgb(30, 144, 255)'
                                        }
                                    },
                                    itemStyle: {
                                        normal: {
                                            color: 'rgb(30, 144, 255)'
                                        }
                                    },
                                },
                                {
                                    xAxisIndex: 1,
                                    yAxisIndex: 1,
                                    name: 'five',
                                    type: 'line',
                                    lineStyle: {
                                        normal: {
                                            width: 2,
                                            color: 'rgb(0, 178, 45)'
                                        }
                                    },
                                    itemStyle: {
                                        normal: {
                                            color: 'rgb(0, 178, 45)'
                                        }
                                    },
                                },
                                {
                                    xAxisIndex: 1,
                                    yAxisIndex: 1,
                                    name: 'fifteen',
                                    type: 'line',
                                    lineStyle: {
                                        normal: {
                                            width: 2,
                                            color: 'rgb(147, 38, 255)'
                                        }
                                    },
                                    itemStyle: {
                                        normal: {
                                            color: 'rgb(147, 38, 255)'
                                        }
                                    },

                                }
                            ],
                            textStyle: {
                                color: '#666',
                                fontSize: 12
                            }
                        }
                        // 使用刚指定的配置项和数据显示图表。
                    Chart.setOption(option);
                    $(window).resize(function() {
                        Chart.resize();
                    });
                    chart['chart-load'] = Chart;
                    return Chart;
                }
            },
            memory: {
                init: function(data) {
                    var Chart = Echarts.init(document.getElementById('chart-memory'), 'walden');
                    var obj = {};
                    obj.unit = '百分比';
                    obj.dataset = {
                        dimensions: ['addtime', 'mem'],
                        source: data
                    };
                    obj.formatter = function(params) {
                        return params[0].value.addtime + '<br>内存:' + params[0].value.mem + '%';
                    }
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
                    chart['chart-memory'] = Chart;
                    return Chart;
                }
            },
            disk: {
                init: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        data[i].read_bytes = (data[i].read_bytes / 1024 / 60).toFixed(3);
                        data[i].write_bytes = (data[i].write_bytes / 1024 / 60).toFixed(3);
                    }
                    var Chart = Echarts.init(document.getElementById('chart-disk'), 'walden');
                    var obj = {};
                    obj.unit = '单位：KB/s';
                    obj.dataset = {
                        dimensions: ['addtime', 'write_bytes', 'read_bytes'],
                        source: data
                    };
                    obj.legend = {
                        data: ['write_bytes', 'read_bytes'],
                        formatter: function(name) {
                            switch (name) {
                                case 'write_bytes':
                                    name = '写入字节数'
                                    break;
                                case 'read_bytes':
                                    name = '读取字节数'
                                    break;
                            }
                            return name;
                        }
                    };
                    obj.formatter = function(params) {
                        return params[0].value.addtime + '<br>读取字节数:' + params[0].value.read_bytes + 'kb/s<br>写入字节数:' + params[0].value.write_bytes + 'kb/s';
                    }
                    obj.series = [{
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        lineStyle: { normal: { width: 1, color: '#aaa' } },
                        itemStyle: { normal: { color: 'rgb(255, 70, 131)' } },
                        areaStyle: { opacity: 0.5 }
                    }, {
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        lineStyle: { normal: { width: 1, color: '#aaa' } },
                        itemStyle: { normal: { color: 'rgb(46, 165, 186)' } },
                        areaStyle: { opacity: 0.5 }
                    }];
                    var option = Controller.api.echarts.getOptions(obj);

                    // 使用刚指定的配置项和数据显示图表。
                    Chart.setOption(option);
                    $(window).resize(function() {
                        Chart.resize();
                    });
                    chart['chart-disk'] = Chart;
                    return Chart;
                }
            },
            net: {
                init: function(data) {
                    var Chart = Echarts.init(document.getElementById('chart-net'), 'walden');
                    var obj = {};
                    obj.unit = '单位：KB/s';
                    obj.dataset = {
                        dimensions: ['addtime', 'up', 'down'],
                        source: data
                    };
                    obj.legend = {
                        data: ['up', 'down'],
                        formatter: function(name) {
                            switch (name) {
                                case 'up':
                                    name = '上行'
                                    break;
                                case 'down':
                                    name = '下行'
                                    break;
                            }
                            return name;
                        }
                    };
                    obj.formatter = function(params) {
                        return params[0].value.addtime + '<br>上行:' + params[0].value.up + 'kb/s<br>下行:' + params[0].value.down + 'kb/s';
                    }
                    obj.series = [{
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        lineStyle: { normal: { width: 1, color: '#aaa' } },
                        itemStyle: { normal: { color: 'rgb(255, 70, 131)' } },
                        areaStyle: { opacity: 0.5 }
                    }, {
                        type: 'line',
                        smooth: true,
                        symbol: 'none',
                        lineStyle: { normal: { width: 1, color: '#aaa' } },
                        itemStyle: { normal: { color: 'rgb(46, 165, 186)' } },
                        areaStyle: { opacity: 0.5 }
                    }];
                    var option = Controller.api.echarts.getOptions(obj);

                    // 使用刚指定的配置项和数据显示图表。
                    Chart.setOption(option);
                    $(window).resize(function() {
                        Chart.resize();
                    });
                    chart['chart-net'] = Chart;
                    return Chart;
                }
            }
        },
        api: {
            echarts: {
                init() {
                    $('.chart-line').each(function(index, element) {
                        element == this;
                        $.ajax({
                            type: "POST",
                            url: "btpanel/ajax/getMonitorData",
                            data: $(element).data(),
                            dataType: "JSON",
                            success: function(res) {
                                switch ($(element).attr('id')) {
                                    case 'chart-load':
                                        Controller.module.load.init(res);
                                        break;
                                    case 'chart-cpu':
                                        Controller.module.cpu.init(res);
                                        break;
                                    case 'chart-memory':
                                        Controller.module.memory.init(res);
                                        break;
                                    case 'chart-disk':
                                        Controller.module.disk.init(res);
                                        break;
                                    case 'chart-net':
                                        Controller.module.net.init(res);
                                        break;
                                    default:
                                        break;
                                }
                            }
                        });
                    });
                },
                getData(id) {
                    $.ajax({
                        type: "POST",
                        url: "btpanel/ajax/getMonitorData",
                        data: $('#' + id).data(),
                        dataType: "JSON",
                        success: function(res) {
                            chart[id].setOption({
                                dataset: {
                                    source: res
                                }
                            });
                        }
                    });
                },
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
                            top: '10%',
                            bottom: 60
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
                            type: 'slider',
                            start: 0,
                            end: 100,
                            zoomLock: true
                        }, {
                            start: 0,
                            end: 100,
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
                }
            },
            daterangepicker: function() {
                require(['bootstrap-daterangepicker'], function() {
                    var options = {
                        timePicker: false,
                        autoUpdateInput: false,
                        timePickerSeconds: true,
                        timePicker24Hour: true,
                        autoApply: true,
                        locale: {
                            format: 'YYYY-MM-DD HH:mm:ss',
                            customRangeLabel: __("Custom Range"),
                            applyLabel: __("Apply"),
                            cancelLabel: __("Clear"),
                        },

                    };
                    var origincallback = function(start, end) {
                        var $chart = $(this.element).parents('.panel').find('.chart-line');
                        $chart.data('start', start.unix());
                        $chart.data('end', end.unix());
                        Controller.api.echarts.getData($chart.attr('id'));
                    };

                    $(".datetimerange").each(function() {
                        var callback = typeof $(this).data('callback') == 'function' ? $(this).data('callback') : origincallback;
                        $(this).on('apply.daterangepicker', function(ev, picker) {
                            callback.call(picker, picker.startDate, picker.endDate);
                        });
                        $(this).on('cancel.daterangepicker', function(ev, picker) {
                            $(this).val('').trigger('blur');
                        });
                        $(this).daterangepicker($.extend(true, options, $(this).data()), callback);
                    });
                });
                $('.label-date').hover(function() {
                    var active = $(this).hasClass("label-active");
                    if (!active) {
                        $(this).removeClass('label-default').addClass('label-success');
                    }
                }, function() {
                    var active = $(this).hasClass("label-active");
                    if (!active) {
                        $(this).removeClass('label-success').addClass('label-default');
                    }
                });
                $('.label-date').click(function() {
                    var active = $(this).hasClass("label-active");
                    if (!active) {
                        $(this).siblings('.label-date').removeClass('label-success label-active').addClass('label-default');
                        $(this).removeClass('label-default').addClass('label-success label-active')
                        var date = $(this).data('date');
                        var $chart = $(this).parents('.panel').find('.chart-line');
                        switch (date) {
                            case 'yesterday':
                                $chart.data('start', Moment().subtract(1, 'days').startOf('day').unix());
                                $chart.data('end', Moment().subtract(1, 'days').endOf('day').unix());
                                Controller.api.echarts.getData($chart.attr('id'));
                                break;
                            case 'today':
                                $chart.data('start', Moment().startOf('day').unix());
                                $chart.data('end', Moment().endOf('day').unix());
                                Controller.api.echarts.getData($chart.attr('id'));
                                break;
                            case 'last7':
                                $chart.data('start', Moment().subtract(6, 'days').startOf('day').unix());
                                $chart.data('end', Moment().subtract(6, 'days').endOf('day').unix());
                                Controller.api.echarts.getData($chart.attr('id'));
                                break;
                            case 'last30':
                                $chart.data('start', Moment().subtract(29, 'days').startOf('day').unix());
                                $chart.data('end', Moment().subtract(29, 'days').endOf('day').unix());
                                Controller.api.echarts.getData($chart.attr('id'));
                                break;
                            default:
                                break;
                        }
                    }
                });
            }
        }
    };
    return Controller;
});