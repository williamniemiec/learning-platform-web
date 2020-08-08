const chart = document.getElementById('chart_progress').getContext('2d');

const progressChart = new Chart(chart, {
    type: 'line',
    data: {
        labels: ['1', '2', '3', '4', '5', '6', '7'],
        datasets: [{
            label: 'Watched classes',
            data: [
				{x: '1', y:10},
				{x: '2', y:7},
				{x: '3', y:3},
				{x: '4', y:0},
				{x: '5', y:4},
				{x: '6', y:12},
				{x: '7', y:9}
			],
			fill:false,
            backgroundColor:'#7d4689',
            borderColor: '#7d4689',
            borderWidth: 1
        }]
    },
    options: {
        responsive:true
    }
});