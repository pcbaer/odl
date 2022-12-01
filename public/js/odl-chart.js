document.addEventListener('readystatechange', () => {
    moment.locale('de');
    odlChart('odl-chart');
});

/**
 * @param id
 */
function odlChart(id) {
    const element = document.getElementById(id);
    const config = window.getComputedStyle(element.getElementsByTagName('span')[0]);
    const canvas = element.getElementsByTagName('canvas')[0];
    let context = canvas.getContext('2d');

    Chart.defaults.global.defaultFontFamily = config.getPropertyValue('font-family');
    Chart.defaults.global.elements.point.pointStyle = 'triangle';
    Chart.defaults.global.elements.point.radius = parseInt(config.getPropertyValue('width'));

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
                        stepSize: parseInt(config.getPropertyValue('line-height'))
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
                pointBorderColor: config.getPropertyValue('color'),
                pointBackgroundColor: config.getPropertyValue('color'),
                backgroundColor: currentColor,
                borderColor: currentColor,
                pointHitRadius: parseInt(config.getPropertyValue('height')),
                spanGaps: true,
                data: currentData
            };
            chart.data.datasets.push(dataset);
        }
        chart.update();
    };
}
