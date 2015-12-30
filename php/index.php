<?php
$start = microtime(true);
include ('charts.php');
?><!DOCTYPE html>
<html>
<head>
	<title>Charts Web App</title>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<?php	//only output dygraph javascript includes etc if using dygraph
	if ( $runCalc ) {
		if ( $dygraph ) {
			echo '	<script type="text/javascript" src="dygraph-combined.js"></script>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7; IE=EmulateIE9">
	<!--[if IE]><script type="text/javascript" src="excanvas.js"></script><![endif]-->' . PHP_EOL;
		} else if ( $googleChart ) {
			echo '<script type="text/javascript" src="https://www.google.com/jsapi"></script>' . PHP_EOL;
			
			echo '
<script type="text/javascript">
    	  google.load("visualization", "1", {packages:["corechart"]});
		  google.setOnLoadCallback(drawChart);
		  function drawChart() {
			var data = new google.visualization.DataTable(
			   {
			     cols: [';
				echo "
				{id: 'date', label: 'Date', type: 'date'}";
				foreach ( $s as $stock ) {
					echo "
				,{id: '$stock[ticker]', label: '$stock[ticker]', type: 'number'}";
				}
				if ( $percentiles ) {
					$pctstr = ",{id: 'low', label: '10th Percentile', type: 'number'},{id: 'mid', label: 'Median', type: 'number'},{id: 'high', label: '90th Percentile', type: 'number'}";
				} else {
					$pctstr = '';
				}
	echo $pctstr . '],

			     rows: [';
						$dates = array_reverse($dates);
						$sCount = count($s);
						$dCount = count($dates);
						if ( $percentiles ) {
							foreach( $dates as $date_count => $date ) {	//build output
								$year = date('Y',$date);
								$month = date('n',$date);
								$day = date('j',$date);
								echo '{c:[{v: ';
								echo 'new Date(' . $year . ', ' . $month . ', ' . $day . ')},';
								$value = $s[0][$t][$date];
								if ( is_null($value) ) $value = "'_'";
								echo '{v: ' . $value . '},{v: ' . $lowPercentile . '},{v: ' . $midPercentile . '},{v: ' . $highPercentile . '}';
								echo ']}';
								if ( $date_count != ($dCount - 1) ) echo ',' . PHP_EOL;
							}
						} else {
							foreach( $dates as $date_count => $date ) {	//build output
								$year = date('Y',$date);
								$month = date('n',$date);
								$day = date('j',$date);
								echo '{c:[{v: ';
								echo 'new Date(' . $year . ', ' . $month . ', ' . $day . ')},';
								foreach( $s as $stock_count => $data ) {	//loop through stock array and output value for date in outter loop
									$value = $data[$t][$date];
									if ( is_null($value) ) $value = "'_'";
									echo '{v: ' . $value . '}';
									if ( $stock_count != ($sCount -1) ) echo ',';
								}
								echo ']}';
								if ( $date_count != ($dCount - 1) ) echo ',' . PHP_EOL;
							}
						}
						$title = "3-year Rolling " . $type_name;
	echo ']
			   }
			)

			var options = {
			  width: 600, height: 400,
			  title: "' . $title . '"
			};

			// Create date formatter
			var formatter_date = new google.visualization.DateFormat({pattern: "MMM yy"});

			// Reformat the data.
			formatter_date.format(data, 0);

			var chart = new google.visualization.LineChart(document.getElementById("chart-div"));
			chart.draw(data, options);
	     }
	     </script>';
		}
	}
?>
	<style>
	.error {	color: red; font-weight: bold; }
	.hide {		display: none; }
	ul {		list-style-type: none; }
<?php
if ( $runCalc  &&  $googleChart ) echo '	#chart-div { border:solid 4px black; border-radius: 5px; width: 750px; padding: 0; }' . PHP_EOL;
?>
	</style>
</head>


<body>

<center><small><?php
if ( $runCalc  &&  $dygraph ) echo '(Click and drag to zoom. Double-click to zoom back out.)';
?></small></center>
<?php
if ( $runCalc  &&  $dygraph ) {
	echo '<div id="chart" style="height:350px;width:750px;margin-left:auto;margin-right:auto;"></div>' . PHP_EOL;
} else if ( $runCalc  &&  $googleChart ) {
	echo '<center><div id="chart-div"></div></center>';
}
?>
<br />

<div id="message">
<?php	if (isset($message)) echo '<span class="error">' . $message . '</span><br />'; ?>
</div>

Please enter a stock ticker:
<ul id="tickers">
	<li class="stocks"><input type="text" id="ticker1" size="10" maxlength="12" />
	<button class="sub">-</button><button class="add">+</button></li>
</ul>


Chart type: <select id="chart-type" onchange="chgType();">
	<option value="beta">Beta</option>
	<option value="stdev">Standard Deviation</option>
	<option value="correl">Correlation</option>
</select>

<ul id="market-tickers" class="hide">
	<li><input type="text" id="market-ticker" size="10" maxlength="12" /></li>
</ul>

<br />
<label><input type="checkbox" checked="checked" id="use-dygraph" /> Use new chart type? <small>(adds zoom, mobile-friendly, may not work in IE)</small></label>
<br /><br />

<input type="button" id="submit-button" name="send" value="Update Chart" />

<br /><br /><small>Please note: calculations are made using weekly data from <b><a target="_blank" href="http://finance.yahoo.com">Yahoo! Finance</a></b>.
<br /><a target="_blank" href="mailto:zacharymueller@gmail.com">Email</a> or <a target="_blank" href="https://www.facebook.com/zach.mueller">Facebook</a> me for comments or requests.
<br />Rolling betas calculated against S&P500 until market input bugs are fixed. Enjoy!
</small>


<script type="text/javascript">
$(".add").live('click',function(){
	var n = $("ul li.stocks").length + 1;
	if ( n <= 5 ) {
		$(".add").remove();
		$(".sub").remove();
		$("#tickers").append('<li class="stocks"><input type="text" id="ticker' + n + '" size="10" maxlength="12" />' + 
		'<button class="sub">-</button><button class="add">+</button></li>');
		$("#ticker" + n).focus();
	} else {
		alert( 'Only up to 5 stocks allowed.' );
	}
	if ( n == 5 ) $(".add").remove();
});
$(".sub").live('click',function(){
	var n = $("ul li.stocks").length - 1;
	if ( n > 0 ) {
		$(".add").remove();
		$(".sub").remove();
		$("#tickers li:last").remove();
		$("#tickers li:last").append('<button class="sub">-</button><button class="add">+</button>');
		$("#ticker" + n).focus();
	}
});

var chgType = function() {
	if ( $('#chart-type').val() == 'correl' ) {
		$('#market-tickers').show();
	} else {
		$('#market-tickers').hide();
	}
};

$("#submit-button").click(function(e){
	var q = '', t = '', m = '', d, type = '', m = '';
	$("#tickers li").each(function(index){
		t = $(this).find('input').val();
		if ( t != '' ) {
			q = q + ',' + t;
		}
	});
	if ( $('#chart-type').val() != '' ) {
		type = '&t=' + $('#chart-type').val();
	}
	if ( $('#use-dygraph').attr('checked') == 'checked' ) {
		d = true;
	} else {
		d = false;
	}
	q.replace('&','');
	if ( q != '' ) {
		q = q.substring(1, q.length);
		if ( $('#market-ticker').val() != '' ) m = '&m=' + $('#market-ticker').val();
		url = 'http://<?php echo $_SERVER['SERVER_NAME'] . str_replace('index.php','',$_SERVER['PHP_SELF']); ?>?' + 'q=' + q + '&d=' + d + type + m;
		window.location = url;
	} else {
		alert( 'Please enter a stock ticker' );
	}
});
</script>
<?php
if ( $runCalc  &&  $dygraph ) {
	echo '<script>
g = new Dygraph(
	document.getElementById("chart"),' . PHP_EOL;
	if ( isset($s) ) {
		echo '
	function() {
		return "" +' . PHP_EOL;
		echo '"Date';
		foreach ( $s as $data ) { echo ',' . $data['ticker']; }
		
		$dateCount = count($dates);
		if ( $percentiles ) {
			echo ',10th Percentile,Median,90th Percentile\n" +' . PHP_EOL;
			$i = 0;
			foreach ( $dates as $date ) {
				if ( $dateCount - $i >= 156 ) {
					$str = '"';
					$str .= date('Y-m-d',$date);
					if ( $s[0][$t][$date] != 0 ) {
						$str .= ',' . round($s[0][$t][$date],5);
					} else {
						$str .= ',null';
					}
					echo $str . ',' . $lowPercentile . ',' . $midPercentile . ',' . $highPercentile . '\n" + ' . PHP_EOL;
				}
				$i++;
			}
		} else {
			echo '\n" +' . PHP_EOL;
			$i = 0;
			foreach ( $dates as $date ) {
				if ( $dateCount - $i >= 156 ) {
					$str = '"';
					$str .= date('Y-m-d',$date);
					foreach ( $s as $data ) {
						if ( $data[$t][$date] != 0 ) {
							$str .= ',' . round($data[$t][$date],5);
						} else {
							$str .= ',null';
						}
					}
					echo $str . '\n" + ' . PHP_EOL;
				}
				$i++;
			}
		}
		echo '"";
	},';
	} else {
		echo '	null,';
	}
	echo '
	{
		title: "3-year Rolling ' . $type_name . '",
		ylabel: "' . $type_name . '",
		legend: "always",
		labelsDivStyles: { "textAlign": "right" }
	}
);
</script>' . PHP_EOL;
}
?>
</body>
</html>
<?php
echo '<!-- Page generated in ' . PHP_EOL;
echo microtime(true) - $start;
echo PHP_EOL . ' seconds -->';
?>
