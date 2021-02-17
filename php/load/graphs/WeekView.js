
function displayView(combiData, plainData, singleDayData, element){

	const baseColors = ['#4E79A7', '#A0CBE8', '#F28E2B', '#FFBE7D', '#59A14F', '#8CD17D', '#B6992D', '#F1CE63', '#499894', '#86BCB6', '#E15759', '#FF9D9A', '#79706E', '#BAB0AC', '#D37295', '#FABFD2', '#B07AA1', '#D4A6C8', '#9D7660', '#D7B5A6'];

	var colorsCategories = {}, catsCount = 0;
	var colorsNames = {}, namesCount = 0;
	plainData.forEach(data => {
		if( !colorsCategories.hasOwnProperty(data.category)){
			colorsCategories[data.category] = baseColors[catsCount % baseColors.length];
			catsCount++;
		}
		if( !colorsNames.hasOwnProperty(data.name)){
			colorsNames[data.name] = baseColors[namesCount % baseColors.length];
			namesCount++;
		}
	});
	var multipleCats = Object.keys(colorsCategories).length > 1;

	function newEvent(name, category, begin, end){
		return new DayPilot.Event({
			start: new DayPilot.Date(new Date(begin * 1000)),
			end: new DayPilot.Date(new Date(end * 1000)),
			id: DayPilot.guid(),
			text: name + (multipleCats ? (" – " + category) : ""),
			backColor: multipleCats ? colorsCategories[category] : colorsNames[name]
		});
	}

	function setup() {
		element.html('<div class="m-2"><div style="display: flex;"><div style="margin-right: 10px;"><div id="nav"></div></div><div style="flex-grow: 1;"><div id="dp"></div></div></div></div>');

		var startDate = new DayPilot.Date(new Date(plainData[0].begin * 1000));

		var nav = new DayPilot.Navigator("nav");
		nav.showMonths = 3;
		nav.selectMode = "week";
		nav.startDate = startDate;
		nav.locale = "de-de";
		nav.onTimeRangeSelected = function (args) {
			dp.startDate = args.start;
			dp.update();
			$(".calendar_default_event_inner").css("color", "black");
		};
		nav.init();

		var dp = new DayPilot.Calendar("dp");
		dp.viewType = "Week";
		dp.startDate = startDate;
		dp.locale = "de-de";
		dp.eventMoveHandling = "Disabled";
		dp.eventResizeHandling = "Disabled";
		dp.timeRangeSelectedHandling = "Disabled";
		dp.init();

		var current = ["","", 0, 0];
		plainData.forEach(data => {
			if(
				data.name === current[0] && data.category === current[1] &&
				Math.abs( current[2] - data.end ) < 120
			){ // group to one "event"?
				current[2] = data.begin;
				return;
			}
			if(current[0] != "" && current[1] != ""){
				dp.events.add(newEvent(...current));
			}
			current = [data.name, data.category, data.begin, data.end];
		});
		if(current[0] != "" && current[1] != ""){
			dp.events.add(newEvent(...current));
		}

		$(".calendar_default_event_inner").css("color", "black");
	}

	if( !window.hasOwnProperty("loadedDayPilot") ){
		$.getScript( loadUrl + "../daypilot/daypilot-all.min.js", () => {
			window.loadedDayPilot = true;
			setup();
		});
	}
	else{
		setup()
	}
	
	return () => {
		element.html('');
	};
}