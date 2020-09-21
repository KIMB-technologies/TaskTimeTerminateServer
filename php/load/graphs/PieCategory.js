function createGraph(data, canvas){
	/**
	 * The DOM has jQuery as $ and the ChartJS library.
	 * The data can be found in data and canvas is the canvas for the chart.
	 * The function should return the ChartJS object.
	 */

	var plotdata = {}
	data.forEach( (v) => {
		if( !plotdata.hasOwnProperty(v.category)){
			plotdata[v.category] = 0
		}
		plotdata[v.category] += v.duration;
	});

	/**
	 * Colors from
	 * 	chartjs-plugin-colorschemes MIT License
	 * 	Copyright (c) 2019 Akihiko Kusanagi
	 * 	https://github.com/nagix/chartjs-plugin-colorschemes/blob/master/src/colorschemes/colorschemes.tableau.js
	 */
	const colors = ['#4E79A7', '#A0CBE8', '#F28E2B', '#FFBE7D', '#59A14F', '#8CD17D', '#B6992D', '#F1CE63', '#499894', '#86BCB6', '#E15759', '#FF9D9A', '#79706E', '#BAB0AC', '#D37295', '#FABFD2', '#B07AA1', '#D4A6C8', '#9D7660', '#D7B5A6']; //['#9999ff', '#993366', '#ffffcc', '#ccffff', '#660066', '#ff8080', '#0066cc', '#ccccff', '#000080', '#ff00ff', '#ffff00', '#0000ff', '#800080', '#800000', '#008080', '#0000ff'];

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
			responsive: true
		}
	};
	return new Chart(canvas, config);
}