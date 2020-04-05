$(function () {
    moment.locale('de');
    odlChart('odl-chart');
});

/**
 * @param id
 */
function odlChart(id) {
    let element = $('#' + id);
    let config = element.find('span');
    let canvas = element.find('canvas')[0];
    let context = canvas.getContext('2d');

    Chart.defaults.global.defaultFontFamily = config.css('font-family');
    Chart.defaults.global.elements.point.pointStyle = 'triangle';
    Chart.defaults.global.elements.point.radius = parseInt(config.css('width'));

    let chart = new Chart(context, {
        type: 'line',
        data: {
            datasets: [{
                label: '',
                pointBorderColor: config.css('color'),
                pointBackgroundColor: config.css('color'),
                backgroundColor: config.css('background-color'),
                borderColor: config.css('background-color'),
                pointHitRadius: parseInt(config.css('height')),
                spanGaps: true,
                data: []
            }]
        },
        options: {
            scales: {
                xAxes: [{
                    type: 'time',
                    distribution: 'linear',
                    time: {
                        displayFormats: {
                            hour: 'D. MMMM YYYY'
                        },
                        tooltipFormat: 'DD.MM.YYYY HH:00',
                        unit: 'hour',
                        stepSize: parseInt(config.css('line-height'))
                    }
                }],
                yAxes: [{
                    ticks: {
                        suggestedMin: 0.0,
                        suggestedMax: 0.2
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'µSv/h'
                    }
                }]
            }
        }
    });

    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'data');
    xhr.responseType = 'json';
    xhr.send();

    xhr.onload = function() {
        let data = xhr.response.data;
        chart.data.datasets[0].data = xhr.response.data;
        chart.data.datasets[0].label = xhr.response.label;
        chart.update();
    };
}
