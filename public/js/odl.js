$(function () {
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

    let chart = new Chart(context, {
        type: 'line',
        data: {
            datasets: [{
                label: '',
                backgroundColor: config.css('background-color'),
                borderColor: config.css('color'),
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
                            hour: 'D.M. H:00'
                        },
                        tooltipFormat: 'DD.MM.YYYY H:00',
                        unit: 'hour',
                        stepSize: 6
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
