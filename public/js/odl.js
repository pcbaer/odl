var canvas = document.getElementById('chart');
var context = canvas.getContext('2d');
var chart = new Chart(context, {
    type: 'line',
    data: {
        datasets: [{
            label: '',
            backgroundColor: '#ffff00',
            borderColor: '#f0f000',
            data: []
        }]
    },
    options: {
        scales: {
            xAxes: [{
                type: 'time',
                time: {
                    displayFormats: {
                        hour: 'D.M. H:00'
                    },
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
xhr.open('GET', '/odl/data');
xhr.responseType = 'json';
xhr.send();

xhr.onload = function() {
    let data = xhr.response.data;
    chart.data.datasets[0].data = xhr.response.data;
    chart.data.datasets[0].label = xhr.response.label;
    chart.update();
};
