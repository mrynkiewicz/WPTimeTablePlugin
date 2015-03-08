
<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<?php echo '<link type="text/css" rel="stylesheet" href="' . plugins_url('tableplugin') . '/plugin.css?'.'"/>'; ?>
<?php wp_enqueue_script('jquery-ui-dialog');?>


<?php
	if(isset($_GET['row']) && isset($_GET['column']) && isset($_GET['eventDescription']))
	{	
		$day = 10*($_GET['column']+4);
		$hour = $_GET['row'] + 3;
		$wpdb->query("INSERT INTO ".$wpdb->prefix."cal_schedule(id, day, hour, description) values(".($day + $hour).",".$day.",".$hour.",'".$_GET['eventDescription']."') ON DUPLICATE KEY UPDATE description=VALUES(description)");
	}
?>


<script>
jQuery(document).ready(function($) {
	$("#myTable td").each(function(index){
		$(this).click(function() {
		var page = "<?php echo $_GET['page']; ?>";
		var col = $(this).parent().children().index($(this));
		var row = $(this).parent().parent().children().index($(this).parent());
		var sendData = { row: row, column: col, action: 'my_action'}; 
		$("#dialog").dialog({ closeOnEscape: true,
								dialogClass: "no-close",
								modal: true,
								show: 'blind',
								hide: 'blind'
		});
		var dialogContent = '<form method="GET" action="<?php echo esc_url(get_permalink()); ?>"><input type="hidden" name="column" value="' + col + '">';
		dialogContent += '<input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">';
		dialogContent += '<input type="hidden" name="row" value="' + row + '">';
		dialogContent += '<div class="day">Dzień: </div>';
		dialogContent += '<div class="hour">Godziny: </div>';
		dialogContent += '<div><div class="smaller"><input id="evtDesc" type="text" name="eventDescription" /></div></div>';
		dialogContent += '<div class="buttons"><input type="submit" value="Zapisz" />';
		dialogContent += '<input type="button" value="Anuluj" id="btnClose" /></div></form>';
		$("p.Information").html(dialogContent); 
		$.ajax({
			url: ajaxurl,
			method: "POST", 
			data: sendData, 
			success: function(response) {
				var obj = $.parseJSON(response);
				$('input[name=eventDescription]').val(obj['content']);
				if ($('input[name=eventDescription]').val().length > 40) {
					$('input[name=eventDescription]').css('color', 'red');
				}
				$('div.day').text('Dzień tygodnia: ' + obj['day'].toUpperCase());
				$('div.hour').text('Godziny zajęć: ' + obj['hour']);
				}
		});
		$("#evtDesc").on('input', function() {
			if ($(this).val().length > 40) {
				$(this).css('color', 'red');
			} else {
				$(this).css('color', 'black');
			}
		});
		$("#btnClose").click(function () {
			window.parent.jQuery("#dialog").dialog('close');
		});
		});
	});
});
</script>

<div id="dialog" title="Modyfikuj wpis"><p class="Information"></p></div>

<div id="message" class="updated fade">
 
<div class="wrap">
	<table id="myTable">
		<thead>
			<tr>
				<th></th>
<?php
$head_results = $wpdb->get_results("SELECT label FROM ".$wpdb->prefix."cal_dayofweek");
foreach($head_results as $result)
{
	echo "<th style=\"border: 1px solid black;\">".strtoupper($result->label)."</th>";
}
?>
			</tr>
		</thead>
<?php
$row_headers = $wpdb->get_results("SELECT id, label FROM ".$wpdb->prefix."cal_hours");
?>
		<tbody>
<?php
foreach($row_headers as $result)
{
	echo "<tr><td style=\"width: 9%; \"><div style=\"min-height: 6em; \">".$result->label."</div></td>";
	for($i = 0; $i < count($head_results); $i++)
	{
		$row_schedule = $wpdb->get_var("SELECT description FROM ".$wpdb->prefix."cal_schedule WHERE hour=".$result->id." AND day=".(10*($i+5)));
		if (!empty($row_schedule))
		{
			echo "<td style=\"border: 1px solid black; width: 13%; padding: 5px; \">".$row_schedule."</td>";	
		}
		else
		{
			echo "<td style=\"border: 1px solid black; width: 13%; \"></td>";
		}
	}	
	echo "</tr>";
}
?>
		</tbody>
	</table>
</div>

