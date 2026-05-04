@extends('admin.layouts.content')

@section('title', '图表示例')

@push('scripts')
    <script src="{{ asset('assets/admin-static/echarts/echarts.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('app').innerHTML = `
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;width:100%;">
                    <div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #ebeef5;">
                        <div style="font-weight:600;color:#303133;margin-bottom:10px;font-size:14px;">折线图</div>
                        <div id="chart-line" style="height:280px;"></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #ebeef5;">
                        <div style="font-weight:600;color:#303133;margin-bottom:10px;font-size:14px;">柱状图</div>
                        <div id="chart-bar" style="height:280px;"></div>
                    </div>
                    <div style="background:#fff;border-radius:10px;padding:16px;border:1px solid #ebeef5;">
                        <div style="font-weight:600;color:#303133;margin-bottom:10px;font-size:14px;">饼状图</div>
                        <div id="chart-pie" style="height:280px;"></div>
                    </div>
                </div>
            `;

            var line = echarts.init(document.getElementById('chart-line'));
            line.setOption({
                tooltip: { trigger: 'axis' },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', boundaryGap: false, data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日'] },
                yAxis: { type: 'value' },
                series: [{
                    name: '访问量',
                    type: 'line',
                    smooth: true,
                    data: [820, 932, 901, 934, 1290, 1330, 1320],
                    areaStyle: { opacity: 0.12 },
                }],
            });

            var bar = echarts.init(document.getElementById('chart-bar'));
            bar.setOption({
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                grid: { left: '3%', right: '4%', bottom: '3%', containLabel: true },
                xAxis: { type: 'category', data: ['华北', '华东', '华南', '西南', '西北'] },
                yAxis: { type: 'value' },
                series: [{ name: '订单', type: 'bar', data: [320, 452, 301, 234, 190], itemStyle: { borderRadius: [4, 4, 0, 0] } }],
            });

            var pie = echarts.init(document.getElementById('chart-pie'));
            pie.setOption({
                tooltip: { trigger: 'item' },
                legend: { bottom: '0', left: 'center' },
                series: [{
                    name: '占比',
                    type: 'pie',
                    radius: ['42%', '68%'],
                    avoidLabelOverlap: true,
                    itemStyle: { borderRadius: 6, borderColor: '#fff', borderWidth: 2 },
                    label: { show: true, formatter: '{b}: {d}%' },
                    data: [
                        { value: 38, name: '类型 A' },
                        { value: 32, name: '类型 B' },
                        { value: 22, name: '类型 C' },
                        { value: 8, name: '其他' },
                    ],
                }],
            });

            window.addEventListener('resize', function () {
                line.resize();
                bar.resize();
                pie.resize();
            });
        });
    </script>
@endpush
