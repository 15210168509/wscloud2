/**
 * Created by dev on 2018/6/1.
 */
$(function () {
    //报警集中时间
    var baseUrl = $('#baseUrl').val();
    var tired_chart_time = echarts.init(document.getElementById('tired_no_time'));
    var tired_time_option =
    {
        legend: {},
        tooltip:{
            trigger:'axis'
        },
        toolbox: {
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
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
        xAxis: {
            type: 'category',
            data: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24]
        },
        yAxis: {
            type: 'value'
        },
        series: []
    };
    tired_chart_time.setOption(tired_time_option);

    $.ajax({
        type: "post",
        url: baseUrl + "/Stat/statByTimeGroup",
        dataType: "json",
        data:{timeType:30},
        success: function(data)
        {
            if(data.code == 1)
            {
                tired_time_option.series = data.data;
                tired_chart_time.setOption(tired_time_option);
            }

        }
    });
});