function createGraph(combiData, plainData, singleDayData, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in the three first data parameters and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	var plotdata = {}
	combiData.forEach( (v) => {
		if( !plotdata.hasOwnProperty(v.name)){
			plotdata[v.name] = 0
		}
		plotdata[v.name] += v.duration;
	});
	Object.keys(plotdata).forEach(function(category) {
		plotdata[category] = Math.round((plotdata[category]/3600) * 100) / 100;
	});

	/**
	 * Colors from
	 * 	chartjs-plugin-colorschemes MIT License
	 * 	Copyright (c) 2019 Akihiko Kusanagi
	 * 	https://github.com/nagix/chartjs-plugin-colorschemes/blob/master/src/colorschemes/colorschemes.tableau.js
	 */
	const baseColors = ['#4E79A7', '#A0CBE8', '#F28E2B', '#FFBE7D', '#59A14F', '#8CD17D', '#B6992D', '#F1CE63', '#499894', '#86BCB6', '#E15759', '#FF9D9A', '#79706E', '#BAB0AC', '#D37295', '#FABFD2', '#B07AA1', '#D4A6C8', '#9D7660', '#D7B5A6'];
	var colors = [];
	do{
		colors = colors.concat(baseColors);
	} while(colors.length < Object.keys(plotdata).length);

	var config = {
		type: 'pie',
		data: {
			datasets: [{
				data: Object.values(plotdata),
				backgroundColor: colors
			}],
			labels: Object.keys(plotdata)
		},
		options: {
			responsive: true,
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chartData) {
						return chartData.labels[tooltipItem.index] + ': ' +
							chartData.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] + ' hours';
					}
				}
			}
		}
	};
	return new Chart(canvas, config);
}