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
            datasets: [],
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
        const labels = xhr.response['labels'];
        const data   = xhr.response['data'], n = data.length;
        const colors = xhr.response['colors'];
        const own    = xhr.response['own'];

        let c = 0, d = 0, currentLabel, currentData, currentColor, dataset;
        while (d < n) {
            currentColor = colors[c];
            if (c === own) {
                currentLabel = xhr.response['ownLabel'];
                currentData  = xhr.response['gammascout'];
            } else {
                currentLabel = labels[d];
                currentData  = data[d];
                d++;
            }
            c++;
            dataset = {
                label: currentLabel,
                pointBorderColor: config.css('color'),
                pointBackgroundColor: config.css('color'),
                backgroundColor: currentColor,
                borderColor: currentColor,
                pointHitRadius: parseInt(config.css('height')),
                spanGaps: true,
                data: currentData
            };
            chart.data.datasets.push(dataset);
        }
        chart.update();
    };
}
