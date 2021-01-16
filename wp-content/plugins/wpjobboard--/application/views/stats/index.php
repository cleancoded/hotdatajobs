<?php $this->slot("title", "Earning Statistics"); ?>

<div class="clear">&nbsp;</div>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {packages:['imagelinechart']});
google.setOnLoadCallback(Wpjb.ChartLoaded);


</script>

<form action="" method="post">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td colspan="2" class="wpjb-form-spacer"><h3>Chart Options</h3></td>
        </tr>
        <tr valign="top">
            <th scope="row">Show</th>
            <td class="wpjb-normal-td">
                <select name="type" id="statstype">
                    <option value="1">Earnings</option>
                    <option value="2">Job Posted</option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">Start Date</th>
            <td class="wpjb-normal-td">
                <input type="text" name="start" id="stats_start" value="<?php echo date("Y-m-d", strtotime("now -1 MONTH")) ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">End Date</th>
            <td class="wpjb-normal-td">
                <input type="text" name="end" id="stats_end" value="<?php echo date("Y-m-d") ?>" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">&nbsp;</th>
            <td class="wpjb-normal-td">
                <a class="button button-highlighted" id="stats_draw" href="#">
                    wait ...
                </a>
            </td>
        </tr>
        <tr valign="top">
            <td colspan="2" class="wpjb-form-spacer"><h3>Chart</h3></td>
        </tr>
        <tr>
            <td colspan="2">
                <div id="wpjb-chart"></div>
            </td>
        </tr>
        </tbody>
    </table>



</form>
