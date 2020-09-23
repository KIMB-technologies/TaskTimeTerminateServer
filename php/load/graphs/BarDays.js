function createGraph(combiData, plainData, singleDayData, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in the three first data parameters and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	var plotdata = {}
	plainData.forEach( (v) => {
		var day = new Date(v.begin*1000).getDay();
		if( !plotdata.hasOwnProperty(v.category)){
			plotdata[v.category] = []
			for(var d = 0; d < 7; d++){
				plotdata[v.category][d] = 0;
			}
		}
		plotdata[v.category][day] += v.duration;
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
	var index = 0;
	Object.keys(plotdata).forEach( (category) => {
		chartData.datasets.push({
			label : category,
			backgroundColor: baseColors[index % baseColors.length],
			data: plotdata[category]
		});
		index++;
	})
	
	var config = {
		type: 'bar',
		data: chartData,
		options: {
			responsive: true
		}
	};
	return new Chart(canvas, config);
}