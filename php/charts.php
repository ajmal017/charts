<?php

$skipCalc = false;
$runCalc = false;
$message = '';
include ('stat_functions.php');

if ( isset($_GET['q']) ) {
	$runCalc = true;
	
	//check chart type
	if ( isset($_GET['d'])  &&  $_GET['d'] != 'false' ) {
		$dygraph = true; $googleChart = false;
	} else {
		$dygraph = false; $googleChart = true;
	}
	
	//validate form
	if ( $_GET['q'] == '' ) {
		$message .= 'Please enter a stock ticker.<br />' . PHP_EOL;
		$skipCalc = true;
		$runCalc = false;
	} else {
		//extract stock tickers from GET input
		$tickers = explode(',',strtoupper(trim($_GET['q'])));
		$i = 0;
		foreach( $tickers as $ticker ) {
			$s[$i]['ticker'] = $ticker;
			$i++;
		}
		array_splice($s,5);
	}
	
if ( $runCalc ) {
	//gather and set the market ticker value
	if ( $_GET['m'] == '' ) {
		$m[0]['ticker'] = '^GSPC';
	} else {
		$m[0]['ticker'] = strtoupper($_GET['m']);
	}
	
	//gather and set the calculation type
	if ( isset($_GET['t']) ) {
		switch ($_GET['t']) {
			case 'beta':
				$type_name = 'Beta';
				$t = 'beta';
				break;
			case 'stdev':
				$type_name = 'Standard Deviation';
				$t = 'stdev';
				break;
			case 'correl':
				$type_name = 'Correlation';
				$t = 'correl';
				break;
			default:	//default to a beta calculation
				
		}
	} else {
		$t = 'beta';
	}
}
	

if ( $runCalc ) {
	//find todays date values;
	$todayDay = date('d');
	$todayMonth = date('m');
	$todayYear = date('Y');

	//store needed values for query
	$host = "http://ichart.finance.yahoo.com/table.csv";
	$a = 0;				//fromMonth-1
	$b = 01;			//fromDay (two digits)
	$c = 1950;			//fromYear
	$d = $todayMonth - 1;		//toMonth-1
	$e = $todayDay;			//toDay (two digits)
	$f = $todayYear;		//toYear
	$g = 'w';			//d for day, w for weekly, m for month, y for yearly
	
	foreach( $s as $key => $ticker ) {
		$url = $host."?s=".urlencode($ticker['ticker'])."&d=".urlencode($d)."&e=".urlencode($e).
			"&f=".urlencode($f)."&g=".urlencode($g)."&a=".urlencode($a)."&b=".urlencode($b).
			"&c=".urlencode($c)."&ignore=.csv";
		
		/*--------------------------------------------------
		-----OPEN EACH CSV AND STORE DATA INTO ARRAYS-------
		--------------------------------------------------*/
		if ( $file = file($url) ) {
			$n = 1;
			while( array_key_exists($n,$file) ) {
				$line = explode(',',$file[$n]);
				$date = strtotime($line[0]);
				$close = $line[6];
				$s[$key]['prices'][$date] = $close;
				if ( isset($previous_date)  &&  $previous_date != null ) {
					$s[$key]['changes'][$previous_date] = ($s[$key]['prices'][$previous_date] / $s[$key]['prices'][$date]) - 1;
				}
				$dates[$key] = $previous_date;	//store last date value for calculating minimum date
				$previous_date = $date;
				$n++;
			}
		} else {	//file failed to open
			$message .= PHP_EOL . '<br />Failed to find stock ticker "' . $ticker['ticker'] . '"';
		}
		
		$previous_date = null;
		$file = null;
	}
	
	//open market csv and store necessary data
	$url = $host."?s=".urlencode($m[0]['ticker'])."&d=".urlencode($d)."&e=".urlencode($e).
			"&f=".urlencode($f)."&g=".urlencode($g)."&a=".urlencode($a)."&b=".urlencode($b)."&c=".urlencode($c)."&ignore=.csv";
	
	if ( $file = file($url) ) {
		$n = 1;
		while( array_key_exists($n,$file) ) {
			$line = explode(',',$file[$n]);
			$date = strtotime($line[0]);
			$close = $line[6];
			$m[0]['prices'][$date] = $close;
			if ( isset($previous_date)  &&  $previous_date != null ) {
				$m[0]['changes'][$previous_date] = ($m[0]['prices'][$previous_date] / $m[0]['prices'][$date]) - 1;
			}
			$previous_date = $date;
			$n++;
		}
	} else {	//file failed to open
		$message .= PHP_EOL . '<br />Failed to find market ticker "' . $m['ticker'] . '"';
	}
}

if ( $runCalc ) {
	//truncate market array to oldest date from stock data
	$trunc_date = min($dates);
	$dates = null;
	
	//build date array for looping through output
	$i = 0;
	if ( $t == 'beta'  ||  $t == 'stdev'  ||  $t == 'correl' ) {
		foreach( $m[0]['changes'] as $date => $change ) {
			$dates[$i] = $date;
			if ( $date == $trunc_date ) break;
			$i++;
		}
	}
	
	//begin calculations
	switch ($t) {
		case 'correl':
			foreach( $s as $key => $stock ) {
				$i = 0;
				foreach( $stock['changes'] as $date => $change ) {
					$sSlice = array_slice($s[$key]['changes'],$i,156);
					if ( count($sSlice) >= 156 ) {
						$s[$key][$t][$date] = (stat_correlp($sSlice, array_slice($m[0]['changes'],$i,156)));
						$i++;
					}
				}
			}
			break;
		case 'stdev':
			foreach( $s as $key => $stock ) {
				$i = 0;
				foreach( $stock['changes'] as $date => $change ) {
					$sSlice = array_slice($s[$key]['changes'],$i,156);
					if ( count($sSlice) >= 156 ) {	//only calculate when 156 weeks of data exist
						$s[$key][$t][$date] = (stat_stdevp($sSlice)*sqrt(52));
						$i++;
					}
				}
			}
			break;
		default:
			$t = 'beta';
			foreach( $s as $key => $stock ) {
				$i = 0;
				foreach( $stock['changes'] as $date => $change ) {
					$sSlice = array_slice($s[$key]['changes'],$i,156);
					if ( count($sSlice) >= 156 ) {	//only calculate when 156 weeks of data exist
						$s[$key][$t][$date] = ((stat_covarp($sSlice,array_slice($m[0]['changes'],$i,156))) / (stat_varp(array_slice($m[0]['changes'],$i,156))));
						$i++;
					}
				}
			}
	}
	
	if ( count($s) == 1 ) {	//if only one stock being analyzed calc percentiles
		$percentiles = true;
		$lowPercentile = stat_percentile($s[0][$t], .1);
		$midPercentile = stat_percentile($s[0][$t], .5);
		$highPercentile = stat_percentile($s[0][$t], .9);
	} else {
		$percentiles = false;
	}
}

}


/*
	Known bug:
		When stock's most recent date does not match market's most recent date,
		calculations are off (runs beta between offset dates' data)
*/
?>
