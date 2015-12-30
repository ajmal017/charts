<?php

function stat_mean ($data) {
	// calculates mean
	return (array_sum($data) / count($data));
}

function stat_median ($data) {
	// calculates median
	sort ($data);
	$elements = count ($data);
	if (($elements % 2) == 0) {
		$i = $elements / 2;
		return (($data[$i - 1] + $data[$i]) / 2);
	} else {
		$i = ($elements - 1) / 2;
		return $data[$i];
	}
}

function stat_range ($data) {
	// calculates range
	return (max($data) - min($data));
}

function stat_var ($data) {
	// calculates sample variance
	$n = count ($data);
	$mean = stat_mean ($data);
	$sum = 0;
	foreach ($data as $element) {
		$sum += pow (($element - $mean), 2);
	}
	return ($sum / ($n - 1));
}

function stat_varp ($data) {
	// calculates population variance
	$n = count ($data);
	$mean = stat_mean ($data);
	$sum = 0;
	foreach ($data as $element) {
		$sum += pow (($element - $mean), 2);
	}
	return ($sum / $n);
}

function stat_stdev ($data) {
	// calculates sample standard deviation
	return sqrt (stat_var($data));
}

function stat_stdevp ($data) {
	// calculates population standard deviation
	return sqrt(stat_varp($data));
}

function stat_covarp ($data1, $data2) {
	//calculates population covariance
	//(1/n)*SUM((x1 - mean1) * (x2 - mean2))
	//must be same size arrays
	$mean1 = stat_mean($data1);
	$mean2 = stat_mean($data2);
	$sum = 0;
	for ($i = 0; $i < count($data1); $i++) {
			$sum += ($data1[$i] - $mean1)*($data2[$i] - $mean2);
	}
	return ($sum) / count($data1);
}

function stat_correlp ($data1, $data2) {
	//calculates the population correlation
	//arrays must be same size
	$covarp = stat_covarp($data1, $data2);
	$stdevp1 = stat_stdevp($data1);
	$stdevp2 = stat_stdevp($data2);
	return ($covarp / ($stdevp1 * $stdevp2));
}

function stat_simple_regression ($x, $y) {
// runs a simple linear regression on $x and $y
// returns an associative array containing the following fields:
// a - intercept
// b - slope
// s - standard error of estimate
// r - correlation coefficient
// r2 - coefficient of determination (r-squared)
// cov - covariation
// t - t-statistic
	$output = array();
	$output['a'] = 0;
	$n = min (count($x), count($y));
	$mean_x = stat_mean ($x);
	$mean_y = stat_mean ($y);
	$SS_x = 0;
	foreach ($x as $element) {
		$SS_x += pow (($element - $mean_x), 2);
	}
	$SS_y = 0;
	foreach ($y as $element) {
		$SS_y += pow (($element - $mean_y), 2);
	}
	$SS_xy = 0;
	for ($i = 0; $i < $n; $i++) {
		$SS_xy += ($x[$i] - $mean_x) * ($y[$i] - $mean_y);
	}
	$output['b'] = $SS_xy / $SS_x;
	$output['a'] = $mean_y - $output['b'] * $mean_x;
	$output['s'] = sqrt (($SS_y - $output['b'] * $SS_xy)/ ($n - 2));
	$output['r'] = $SS_xy / sqrt ($SS_x * $SS_y);
	$output['r2'] = pow ($output['r'], 2);
	$output['cov'] = $SS_xy / ($n - 1);
	$output['t'] = $output['r'] / sqrt ((1 - $output['r2']) / ($n - 2));

	return $output;
}

function stat_percentile($data, $per) {
	sort($data);
	$denom = 1;
	$count = count($data);
	$i = $per * (($count/$denom) + 1);
	$fi = floor($i);
	if ($i == $fi) {
		return $data[$i];
	} else {
		$ci = ceil($i);
		return ($data[$fi] * 0.5) + ($data[$ci] * 0.5);
	}
}

?>