# WP Easy Pie Chart Plugin

## Description

WordPress plugin to insert [rendro's Easy Pie Chart](https://rendro.github.io/easy-pie-chart/) into your articles and pages with a single shortcode.

### Usage

To display the chart and a percentage value, set "percent" attribute like so: 

``[easypiechart percent="54"]``

![example #1 preview image](./img/example1.jpeg?raw=true)

To display the chart, a percentage and a label, set "percent" (integer) and "label" attributes like so:

``[easypiechart percent="54" label="New Visitors"]``

![example #2 preview image](./img/example2.jpeg?raw=true)

To modify the default appearance of the chart, set "barcolor" (hexadecimal value) and "linewidth" (integer) like so: 

``[easypiechart percent="20" label="Bounce Rate" barcolor="#0d6cA3" linewidth="5"]``

![example #3 preview image](./img/example3.jpeg?raw=true)

For additional parameters, please visit the [Configuration parameter](https://rendro.github.io/easy-pie-chart/) section from within rendro's page or click on "Reading" under the Settings section of your WordPress dashboard:

![reading preview image](./img/reading.jpeg?raw=true)

## Installation

### Manual installation
1. Click on **Add New** from within the Plugins section of your WordPress dashboard. 
2. Click on **Upload Plugin**.
3. Click on **Choose file** and select ["wp-easypiechart.zip"](./wp-easypiechart.zip).
4. Click on **Install Now**.
5. Click on **Activate Plugin**.

### Uninstallation
1. Click on **Deactivate** link from the WordPress Dashboard Plugins menu. 
2. Click on **Delete**.
3. Click on **OK** from within the delete confirmation dialog.

## Licence
Released under the [MIT Licence](LICENSE).