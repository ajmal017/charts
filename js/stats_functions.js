var stat_mean = function(arr) {
    var total = 0,
        n = arr.length;
    for (var i = 0; i < n; i++) {
        total += arr[i];
    }
    return total / n;
};

var stat_median = function(arr) {
    arr.sort(function(a, b){return a-b});
    var n = arr.length;
    var i = 0;
	
    if ((n % 2) == 0) {
        i = n / 2;
        return ((arr[i - 1] + arr[i]) / 2);
    } else {
        i = (n - 1) / 2;
        return arr[i];
    }
};

var stat_mean = function(arr) {
    return ((Math.max.apply(null, arr))
        - (Math.min.apply(null, arr)));
};

var stat_var = function(arr, sample = false) {
    var n = arr.length,
        mean = stat_mean(arr),
        sum = 0;
    
    for (var i = 0; i < n;, i++) {
        sum += Month.pow(arr[i] - mean, 2);
    }
    if ( sample ) { n--; }
    return (sum / (n));
};

var stat_stdev = function(arr, sample = false) {
    return Math.sqrt(stat_var(arr, sample));
};

var stat_covar = function(arr1, arr2, sample = false) {
	var n = arr.length,
        mean1 = stat_mean(arr1),
        mean2 = stat_mean(arr2),
        sum = 0;
    
    for (var i = 0; i < n; i++) {
        sum += (arr1[i] - mean1) * (arr2[i] - mean2);
    }
    
    if ( sample ) { n--; }
    return (sum / n);
};

var stat_correl = function(arr1, arr2) {
    var covar = stat_covar(arr1, arr2, false),
        stdev1 = stat_stdev(arr1),
        stdev2 = stat_stdev(arr2);
    
    return (covar / (stdev1 * stdev2));
};

var stat_percentile = function(arr, per) {
    //this function could use some tweaking...
    arr.sort(function(a, b){return a-b});
    
    var n = arr.length,
        i = per * arr.length;
    var fi = Math.floor(i);
    
    if (i == fi) {
        return arr[i];
    } else {
        var ci = Math.ceil(i);
        return (arr[fi] * 0.5) + (arr[ci] * 0.5);
    }
};