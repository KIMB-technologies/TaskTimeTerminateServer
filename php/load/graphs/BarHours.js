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

	var plotdata = {}
	plainData.forEach( (v) => {
		var begin = new Date(v.begin*1000);
		var hourBegin = begin.getHours();
		var secondsBegin = begin.getMinutes() * 60 + begin.getSeconds();

		var end = new Date(v.end*1000);
		var hourEnd = end.getHours();
		var secondsEnd = end.getMinutes() * 60 + end.getSeconds();

		for( var hour = hourBegin; hour <= hourEnd; hour++){
			if( !plotdata.hasOwnProperty(v[catsColumn])){
				plotdata[v[catsColumn]] = []
				for(var h = 0; h < 24; h++){
					plotdata[v[catsColumn]][h] = 0;
				}
			}

			if( hour !== hourEnd && hour !== hourBegin ){
				plotdata[v[catsColumn]][hour] += 3600;
			}
			else if (hour === hourBegin && hour === hourEnd ){
				plotdata[v[catsColumn]][hour] += v.duration;
			}
			else if( hour === hourEnd ){
				plotdata[v[catsColumn]][hour] += secondsEnd;
			}
			else if( hour === hourBegin){
				plotdata[v[catsColumn]][hour] += 3600 - secondsBegin;
			}
		}
	});

	stacksMap = {}
	Object.keys(plotdata).forEach(function(category) {
		for(var h = 0; h < 24; h++){
			plotdata[category][h] = Math.round((plotdata[category][h]/3600) * 100) / 100;
		}
		if(!stacksMap.hasOwnProperty(category)){
			let pos = category.indexOf('::');
			stacksMap[category] = ( pos === -1 ? '' : category.substr(0, pos) );
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
		labels : [...Array(24).keys()].map( (k) => {
			return (k < 10 ? '0' : '' ) + k;
		}),
		datasets: []
	};
	Object.keys(plotdata).forEach( (category, index) => {
		chartData.datasets.push({
			label : category,
			backgroundColor: baseColors[index % baseColors.length],
			data: plotdata[category],
			stack : stacksMap[category]
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
						return tooltipItem[0].label + ':00 - ' + tooltipItem[0].label + ':59: ' + Math.round(chartData.datasets.reduce((p,c) => p + c.data[tooltipItem[0].index], 0) * 100) / 100 + ' hours';
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