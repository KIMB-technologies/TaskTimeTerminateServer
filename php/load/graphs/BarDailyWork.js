function createGraph(combiData, plainData, singleDayData, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in the three first data parameters and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	let plotdata = {};
	plainData.forEach(data => {
		let date = new Date(data.begin * 1000);
		let day = (date.getDate() > 9 ? '' : '0') + date.getDate();
		let month = (date.getMonth() + 1 > 9 ? '' : '0') + (date.getMonth() + 1);
		
		let key = `${day}.${month}.${date.getFullYear()}`;
		if (!plotdata.hasOwnProperty(key)) {
			plotdata[key] = 0;
		}

		plotdata[key] += data.duration;
	});

	Object.keys(plotdata).forEach(key => {
		plotdata[key] = Math.round((plotdata[key] / 3600) * 100) / 100;
	});

	let plotdataSorted = {};
	Object.keys(plotdata).sort().forEach(key => {
		plotdataSorted[key] = plotdata[key];
	});
	
	/**
	 * Colors from
	 * 	chartjs-plugin-colorschemes MIT License
	 * 	Copyright (c) 2019 Akihiko Kusanagi
	 * 	https://github.com/nagix/chartjs-plugin-colorschemes/blob/master/src/colorschemes/colorschemes.tableau.js
	 */
	let config = {
		type: 'bar',
		data: {
			datasets: [{
				data: Object.values(plotdataSorted),
				label: 'Daily work',
				backgroundColor: '#A0CBE8',
				borderColor: '#4E79A7',
				borderWidth: 1
			}],
			labels: Object.keys(plotdataSorted)
		},
		options: {
			responsive: true,
			tooltips: {
				callbacks: {
					label: function(tooltipItem, chartData) {
						return `Daily work: ${chartData.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]} hours`;
					}
				}
			}
		}
	};

	return new Chart(canvas, config);
}