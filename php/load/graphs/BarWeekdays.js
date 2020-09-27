function createGraph(combiData, plainData, singleDayData, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in the three first data parameters and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	var allCategories = []
	plainData.forEach(data => {
		if( !allCategories.includes(data.category) ) {
			allCategories.push(data.category)
		}
	});
	var catsColumn = allCategories.length === 1 ? 'name' : 'category';

	var dayMap = {
		0:6,
		1:0,
		2:1,
		3:2,
		4:3,
		5:4,
		6:5,
	}
	var plotdata = {}
	plainData.forEach( (v) => {
		var day = dayMap[new Date(v.begin*1000).getDay()];
		if( !plotdata.hasOwnProperty(v[catsColumn])){
			plotdata[v[catsColumn]] = []
			for(var d = 0; d < 7; d++){
				plotdata[v[catsColumn]][d] = 0;
			}
		}
		plotdata[v[catsColumn]][day] += v.duration;
	});

	Object.keys(plotdata).forEach(function(category) {
		for(var d = 0; d < 7; d++){
			plotdata[category][d] = Math.round((plotdata[category][d]/3600) * 100) / 100;
		}
	});

	/**
	 * Colors from
	 * 	chartjs-plugin-colorschemes MIT License
	 * 	Copyright (c) 2019 Akihiko Kusanagi
	 * 	https://github.com/nagix/chartjs-plugin-colorschemes/blob/master/src/colorschemes/colorschemes.tableau.js
	 */
	const baseColors = ['#4E79A7', '#A0CBE8', '#F28E2B', '#FFBE7D', '#59A14F', '#8CD17D', '#B6992D', '#F1CE63', '#499894', '#86BCB6', '#E15759', '#FF9D9A', '#79706E', '#BAB0AC', '#D37295', '#FABFD2', '#B07AA1', '#D4A6C8', '#9D7660', '#D7B5A6'];

	var chartData = {
		labels : ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
		datasets: []
	};
	Object.keys(plotdata).forEach( (category, index) => {
		chartData.datasets.push({
			label : category,
			backgroundColor: baseColors[index % baseColors.length],
			data: plotdata[category]
		});
	})
	
	var config = {
		type: 'bar',
		data: chartData,
		options: {
			responsive: true,
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chartData) {
						return `${chartData.datasets[tooltipItem.datasetIndex].label} ${chartData.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]} hours`;
					},
					title : function(tooltipItem, chartData) {
						return tooltipItem[0].label + ': '+ Math.round(chartData.datasets.reduce((p,c) => p + c.data[tooltipItem[0].index], 0) * 100) / 100 + ' hours';
					}
				}
			},
			scales: {
				xAxes: [{
					stacked: true,
					beginAtZero: true
				}],
				yAxes: [{
					stacked: true
				}]
			}
		}
	};
	return new Chart(canvas, config);
}