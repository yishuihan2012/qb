/**
 * Created by Administrator on 2016/8/18.
 */
$(function() {
    Morris.Line({
        element: "morris-one-line-chart",
        data: [{year: "2008",value: 5},{year: "2009",value: 10},{year: "2010",value: 8},{year: "2011",value: 22},{year: "2012", value: 8},{year: "2014",value: 10},{year: "2015", value: 5}],
        xkey: "year",
        ykeys: ["value"],
        resize: !0,
        lineWidth: 4,
        labels: ["Value"],
        lineColors: ["#1ab394"],
        pointSize: 5
    }),
        Morris.Donut({
            element: "morris-donut-chart",
            data: [{label: "已支付",value: 12},{label: "未支付", value: 30}],
            resize: !0,
            colors: ["#87d6c6", "#54cdb4"]
        }),
        Morris.Bar({
            element: "morris-bar-chart",
            data: [{y: "2006", a: 60}, {y: "2007", a: 75}, {y: "2008", a: 50}, {y: "2009", a: 75}, {y: "2010", a: 50},{y: "2011", a: 75}, {y: "2012", a: 100}],
            xkey: "y",
            ykeys: ["a"],
            labels: ["投保人数"],
            hideHover: "auto",
            resize: !0,
            barColors: ["#1ab394"]
        })
});
