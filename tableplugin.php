<?php
/*
 * Plugin Name: Grafik Zajęć
 * Description: Plugin służący do edycji i wyświetlania tabeli z grafikiem zajęć
 * Author: Marcin Rynkiewicz
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function register_plugin_styles() {
	wp_register_style( 'table-plugin', plugins_url( 'tableplugin/plugin.css' ) );
	wp_enqueue_style( 'table-plugin' );
}

add_action( 'wp_enqueue_scripts', 'register_plugin_styles' );

function tableplugin_install()
{
	global $wpdb;
	$table = $wpdb->prefix."cal_dayofweek";
	$structure = "CREATE TABLE $table (
        id INT(9) NOT NULL,
        label VARCHAR(10) NOT NULL,
	PRIMARY KEY (id)
    );";
    $wpdb->query($structure);
	
	$structure = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
	$wpdb->query($structure);
 
    $wpdb->query("INSERT INTO $table(id, label)
        VALUES(50, 'pn')");
    $wpdb->query("INSERT INTO $table(id, label)
        VALUES(60, 'wt')");
	$wpdb->query("INSERT INTO $table(id, label)
        VALUES(70, 'śr')");
	$wpdb->query("INSERT INTO $table(id, label)
        VALUES(80, 'cz')");
	$wpdb->query("INSERT INTO $table(id, label)
        VALUES(90, 'pt')");
	$wpdb->query("INSERT INTO $table(id, label)
        VALUES(100, 'sb')");
	$wpdb->query("INSERT INTO $table(id, label)
        VALUES(110, 'nd')");
		
	$table = $wpdb->prefix."cal_hours";
	$structure = "CREATE TABLE $table (
		id INT(9) NOT NULL,
		label VARCHAR(20) NOT NULL,
	PRIMARY KEY (id)
	);";
	$wpdb->query($structure);
	
	$structure = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
	$wpdb->query($structure);
	
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(3, '10:00-11:00')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(4, '11:00-12:00')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(5, '12:00-13:15')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(6, '15:00-16:00')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(7, '16:00-17:00')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(8, '17:00-18:00')");
	$wpdb->query("INSERT INTO $table(id, label)
		VALUES(9, '18:00-19:00')");
		
	$table = $wpdb->prefix."cal_schedule";
	$structure = "CREATE TABLE $table (
		id INT(9) NOT NULL,
		day INT(9) NOT NULL,
		hour INT(9) NOT NULL,
		description VARCHAR(100),
	PRIMARY KEY (id)
	);";
	$wpdb->query($structure);
	
	$structure = "ALTER TABLE $table DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci";
	$wpdb->query($structure);
	
}

function plugin_uninstall()
{
	global $wpdb;
	
	$wpdb->query("DROP TABLE ".$wpdb->prefix."cal_schedule, ".$wpdb->prefix."cal_hours, ".$wpdb->prefix."cal_dayofweek");
	
}

add_action('activate_tableplugin/tableplugin.php', 'tableplugin_install');
register_deactivation_hook( __FILE__, 'plugin_deactivate' );

function tableplugin_menu()
{
    global $wpdb;
    include 'tableplugin-admin.php';
}
 
function tableplugin_admin_actions()
{
    add_menu_page("Opcje Tabeli", "Edycja Tabeli", 'edit_pages',
"Tabela-Opcje", "tableplugin_menu");
}
 
add_action('admin_menu', 'tableplugin_admin_actions');

add_action( 'wp_ajax_my_action', 'process_ajax' );
function process_ajax()
{
	global $wpdb;
	
	$day = 10*($_POST['column'] + 4);
	$hour = $_POST['row'] + 3;

	ob_clean();
	//echo $wpdb->get_var("SELECT description FROM ".$wpdb->prefix."cal_schedule WHERE id=".($day + $hour));
	//echo $day;
	
	$results['content'] = $wpdb->get_var("SELECT description FROM ".$wpdb->prefix."cal_schedule WHERE id=".($day + $hour));
	$results['day'] = $wpdb->get_var("SELECT label FROM ".$wpdb->prefix."cal_dayofweek WHERE id=".$day);
	$results['hour'] = $wpdb->get_var("SELECT label FROM ".$wpdb->prefix."cal_hours WHERE id=".$hour);
	
	echo json_encode($results);
	
	wp_die();
}

function draw_on_page()
{
	
	global $wpdb;
	
	$jqueryCode = "<script>jQuery(document).ready(function($) {";
	$jqueryCode .= "$(\".event-desc\").bind(\"mouseenter\", function(e) {";
	$jqueryCode .= "$(\"#ToolTipDiv\").offset({left: e.pageX, top: e.pageY});";
	$jqueryCode .= "$(\"#ToolTipDiv\").show(\"normal\");";
	$jqueryCode .= "$(this).attr('data-title', $(this).attr('title'));";
	$jqueryCode .= "$(this).removeAttr('title');";	
	$jqueryCode .= "$(\"#ToolTipDiv\").text($(this).attr(\"data-title\"));";
	$jqueryCode .=	"}); ";
	$jqueryCode .= "$('.event-desc').bind('mouseleave', function (e) { "; 
	$jqueryCode .= "$('#ToolTipDiv').text(''); }); }); </script>";
	$jqueryCode .= "<div id=\"ToolTipDiv\">";
	$jqueryCode .= "To jest tekst z tooltipa</div>";
	
	$tableHTML = "<table class=\"plugin-table\"><thead><tr><th class=\"plugin-table\"></th>";
	
	$head_results = $wpdb->get_results("SELECT label FROM ".$wpdb->prefix."cal_dayofweek");
	foreach($head_results as $result)
	{
		$tableHTML .= "<th class=\"plugin-table\">".strtoupper($result->label)."</th>";
	}
	
	$tableHTML .= "</tr></thead>";
	
	$row_headers = $wpdb->get_results("SELECT id, label FROM ".$wpdb->prefix."cal_hours");
	foreach($row_headers as $result)
	{
		$tableHTML .= "<tr><td class=\"hours-column\">".$result->label."</div></td>";
		for($i = 0; $i < count($head_results); $i++)
		{
			$row_schedule = $wpdb->get_var("SELECT description FROM ".$wpdb->prefix."cal_schedule WHERE hour=".$result->id." AND day=".(10*($i+5)));
			if (!empty($row_schedule))
			{
				$tableHTML .= "<td class=\"event-desc\" title=\"".$row_schedule."\">".substr($row_schedule, 0, 40)."</td>";	
			}
			else
			{
				$tableHTML .= "<td class=\"event-desc\"></td>";
			}
	}	
	$tableHTML .= "</tr>";
}
	
	
	$tableHTML .= "</table>";
	
	echo $tableHTML;
	echo $jqueryCode;
	
}

add_shortcode('schedule', 'draw_on_page'); 

?>