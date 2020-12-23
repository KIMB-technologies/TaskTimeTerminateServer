# TaskTimeTerminate-SyncServer
> A docker image providing a sync-server for TaskTimeTerminate

## Features
- Server Task
	- Add Tasks directly on the server
- Statistics
	- Show stats one the server
	- Show charts made with ChartJS 
- Account Management
	- Each user gets an account, also called group
	- This account can have multiple devices
- Device Management
	- Add, edit and delete devices

## Install on server
This is a docker project, so it you will need docker to run it.
It is recommended to use the [docker-compose.yml](https://github.com/KIMB-technologies/TaskTimeTerminateServer/blob/master/docker-compose.yml) provided.

1. Copy the [docker-compose.yml](https://github.com/KIMB-technologies/TaskTimeTerminateServer/blob/master/docker-compose.yml)
2. Make sure to bind to a free port
3. Change the volume to a location wich is regularly made a backup from. (Inside the container all data is stored in `/php-code/data/`)
4. Edit environment variables
	- `DEVMODE` should always be `false` (enables error messages)
      - `CONF_DOMAIN` the url where the service will be hosted (what the webbrowser sees)
      - `CONF_TIMEZONE` a PHP timezone string, matching the location of the server users (see https://www.php.net/manual/en/timezones.php for list of supported ones)
      - `ADMIN_ACCOUNT` username/ groupname of the initial account (will be created on first container startup; will be an admin user)
      - `ADMIN_PASSWORD` password for the initial account
5. Run the container and log into the Webinterface

> One may delete `ADMIN_ACCOUNT` and `ADMIN_PASSWORD` after the account is created (and also change the password),
> If `ADMIN_ACCOUNT` is set and the account already exists, the system will overwrite the password with `ADMIN_PASSWORD`.

## Setup TTT-Client
### TTT-Client
> See https://github.com/KIMB-technologies/TaskTimeTerminate for more information about TTT.

1. Log into the Webinterface and go to `Device Management`
2. Add a new device and remember its token.
3. Run `ttt conf sync server` in your client
4. Type the URL of this installation, the account name/ group, the client token and the devices/ clients name.
5. The client will import all recorded data to the installation and also update it with new completed task.

## Im & Export
This repository contains a script to import data from other devices to the server
and also a script that exports the data of all devices to a directory.
The format used is the same as used in the local directory synchronisation.

So one may transfer the the data from an old client (not used anymore, used clients will
automatically import their data when they are connected to the server for the first time)
to the server.

### Import to Server
Create the the device for which the data should be imported and 
remember its token. (The name of the device can only be set once via the Webinterface).

1. Run `php ./imexport/import.php`
2. Type credentials of device when prompted
3. Fill in path to the directory containing the devices data (e.g. `2020-04-01.json`, `2020-04-03.json`, ...)
4. Change the timezone, if you want (see https://www.php.net/manual/en/timezones.php for list of supported zones)
5. The data for the device will be available in the Webinterface (and through the API).

### Export from Server
One may use any device of an account, but the device will only get data from other devices 
and no data from itself. (So create a new device for a full export!)

1. Run `php ./imexport/export.php`
2. Type credentials of device when prompted
3. Change the export directory if you want (it has to be any empty directory and it will be created if not exists). 
4. The data for all devices will show up the directory

## Custom Graphs
It is possible to create custom graph-views for the stats view.

### Create your own stats function
> See an example e.g. [PieCategory](https://github.com/KIMB-technologies/TaskTimeTerminateServer/blob/master/php/load/graphs/Pie.js)

When the user selects a graph and clicks on *Display Graph* the corresponding JS-file is loaded and the the function `createGraph(combiData, plainData, singleDayData, canvas)`
of this file will be executed. In a file like [PieCategory](https://github.com/KIMB-technologies/TaskTimeTerminateServer/blob/master/php/load/graphs/Pie.js)
such function like `createGraph(combiData, plainData, singleDayData, canvas)` can be defined.

Each function gets four parameters, three `*Data` and one `canvas`.
The parameter `*Data` are arrays containing objects with datasets, they all represent the same current data selection of the user, but haven different formats.
- `combiData`
	- `[{"category":"Test","name":"My Task","duration":3600,"times":1,"days":["23.09", ...],"devices":["Server", ...]}, ...]`
- `plainData`
	- This array may be very large and the system may not deliver all user selected datasets (when they become too much).
	- `[{"name":"My Task","category":"Test","begin":1600866000,"end":1600869600,"duration":3600,"device":"Server"}, ...]`
- `singleDayData`
	- Will be `false` if more than one day selected by user.
	- `[{"Begin":"15:00","Category":"Test","Name":"My Task","Time":"      1h  0m"}, ...]`

The object `canvas` is a connector to the canvas on the page, where the chart should show up.
It can be directly used with `new Chart(canvas, chartData)`.

The function *must* return the created `Chart` object, cause the stats page need access to it.
So a typical wokflow will use `data` to generate a object `chartData` for ChartJS and 
end with `return new Chart(canvas, chartData);`.

For information how to create charts see https://www.chartjs.org/ (also jQuery is loaded in the DOM as `$`).

### Upload to server
All JS file creating charts (and containing exactly one `createGraph`) have to be placed in the folder
`/php-code/load/graphs/` inside the docker-container. Using docker-compose one might add
`./charts/:/php-code/load/graphs/` to the `volumes` section of the `docker-compose.yml` and 
add all file to `./charts/`.

All JS file should be named like this `<GraphName>.js`. Of course it it recommended to 
use only `A-Za-z-9` for the `GraphName`.