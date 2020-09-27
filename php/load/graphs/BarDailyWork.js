function createGraph(combiData, plainData, singleDayData, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in the three first data parameters and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	function getLabelFromTimestamp(t) {
		let date = new Date(t * 1000);
		let day = (date.getDate() > 9 ? '' : '0') + date.getDate();
		let month = (date.getMonth() + 1 > 9 ? '' : '0') + (date.getMonth() + 1);

		return `${day}.${month}.${date.getFullYear()}`;
	}

	// get time span and all categories
	var minDate = Number.MAX_SAFE_INTEGER;
	var maxDate = Number.MIN_SAFE_INTEGER;
	var allCategories = {}
	plainData.forEach(data => {
		if( data.begin < minDate ){
			minDate = data.begin;
		}
		if( data.end > maxDate ){
			maxDate = data.end;
		}
		if( !allCategories.hasOwnProperty(data.category)){
			allCategories[data.category] = 0;
		}
	});

	// fill (empty) categories in each day
	let plotdata = {};
	let plotdataLabels = [];
	for( let timestamp = minDate; timestamp <= maxDate; timestamp += 86400){
		let label = getLabelFromTimestamp(timestamp);
		plotdata[label] = Object.assign({}, allCategories);
		plotdataLabels.push(label)
	}
	let lastLabel = getLabelFromTimestamp(maxDate);
	if( !plotdata.hasOwnProperty(lastLabel)){
		plotdata[lastLabel] = Object.assign({}, allCategories);
		plotdataLabels.push(lastLabel)
	}

	// fill with data
	plainData.forEach(data => {
		plotdata[getLabelFromTimestamp(data.begin)][data.category] += data.duration;
	});

	// convert to hours
	Object.keys(plotdata).forEach(function(label) {
		Object.keys(plotdata[label]).forEach(function(category) {
			plotdata[label][category] = Math.round((plotdata[label][category] / 3600) * 100) / 100;
		});
	});

	/**
	 * Colors from
	 * 	chartjs-plugin-colorschemes MIT License
	 * 	Copyright (c) 2019 Akihiko Kusanagi
	 * 	https://github.com/nagix/chartjs-plugin-colorschemes/blob/master/src/colorschemes/colorschemes.tableau.js
	 */
	const baseColors = ['#4E79A7', '#A0CBE8', '#F28E2B', '#FFBE7D', '#59A14F', '#8CD17D', '#B6992D', '#F1CE63', '#499894', '#86BCB6', '#E15759', '#FF9D9A', '#79706E', '#BAB0AC', '#D37295', '#FABFD2', '#B07AA1', '#D4A6C8', '#9D7660', '#D7B5A6'];

	var categoryDatasetIdMap = {};
	var chartData = {
		labels : plotdataLabels,
		datasets: []
	};

	var datasetIndex = 0;
	plotdataLabels.forEach(function(label) {
		Object.keys(plotdata[label]).forEach(function(category) {
			if(!categoryDatasetIdMap.hasOwnProperty(category)){
				categoryDatasetIdMap[category] = datasetIndex;
				chartData.datasets.push({
					label : category,
					backgroundColor: baseColors[datasetIndex % baseColors.length],
					data: []
				});
				datasetIndex++;
			}
			chartData.datasets[categoryDatasetIdMap[category]].data.push(
				plotdata[label][category]
			);
		});
	});
	
	let config = {
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