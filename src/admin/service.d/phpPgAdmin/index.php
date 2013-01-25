<?php

include_once ('../../../WHAT/Lib.Prefix.php');
include_once ('../../../WHAT/Lib.Common.php');

include_once("phpPgAdmin/conf/config.inc.php");

$error = '';

try {
	if (isset($_REQUEST['save'])) {
		save_posted_config($conf);
		reload();
	} else if (isset($_REQUEST['autodetect'])) {
		autodetect_pg_service($conf);
		reload();
	}
} catch(Exception $e) {
	$error = $e->getMessage();
}

function p($s) {
	print($s);
}

function e($s) {
	return htmlspecialchars($s);
}

function pe($s) {
	p(e($s));
}

function saveConfigInc(&$conf) {
	$c = '';
	$c .= '<?php' . PHP_EOL;
	$c .= '$conf = ' . var_export($conf, true) . ';' . PHP_EOL;
	if (file_put_contents("phpPgAdmin/conf/config.inc.php", $c) === false) {
		throw new Exception("Error writing to 'phpPgAdmin/conf/config.inc.php'.");
	}
}

function save_posted_config(&$conf) {
	foreach ($conf['servers'][0] as $k => &$v) {
		if (isset($_REQUEST[$k])) {
			$conf['servers'][0][$k] = $_REQUEST[$k];
		}
	}
	saveConfigInc($conf);
}

function autodetect_pg_service(&$conf) {
	$service_name = getDbAccessValue('pgservice_core');
	if ($service_name == '') {
		throw new Exception("Got empty service name for 'pgservice_core'.");
	}
	if (!isset($_REQUEST['_pg_service_file'])) {
		throw new Exception("Missing '_pg_service_file'.");
	}
	$f = $_REQUEST['_pg_service_file'];
	if (!is_file($f)) {
		throw new Exception(sprintf("'%s' does not exists.", $f));
	}
	$ini = parse_ini_file($f, true);
	if ($ini === false) {
		throw new Exception(sprintf("Error parsing file '%s'.", $f));
	}
	if (!isset($ini[$service_name])) {
		throw new Exception(sprintf("Service with name '%s' not found in '%s'.", $pgservice_core, $f));
	}
	foreach (array(
		'host' => 'host',
		'port' => 'port',
		'dbname' => 'defaultdb',
		'sslmode' => 'sslmode'
	) as $kI => $kC) {
		if (isset($ini[$service_name][$kI])) {
			$conf['servers'][0][$kC] = $ini[$service_name][$kI];
		}
	}
error_log(print_r($conf, true));
	saveConfigInc($conf);
}

function reload() {
	header('Location: ' . $_SERVER['PHP_SELF']);
	exit(0);	
}

?>
<html>
<head>
<title>phpPgAdmin configuration</title>
<style>
.error_message {
	color: red;
}
</style>
</head>
<body>

<div class="error_message">
<pre><?php pe($error) ?></pre>
</div>

<div>
<p>Parameters:</p>
<form action="<?php pe($_SERVER['PHP_SELF'] . '?save') ?>" method="post">
<pre>
'host'      <input type="text" name="host" value="<?php pe($conf['servers'][0]['host']) ?>" />
'port'      <input type="text" name="port" value="<?php pe($conf['servers'][0]['port']) ?>" />
'sslmode'   <input type="text" name="sslmode" value="<?php pe($conf['servers'][0]['sslmode']) ?>" />
'defaultdb' <input type="text" name="defaultdb" value="<?php pe($conf['servers'][0]['defaultdb']) ?>" />
</pre>
<input type="submit" value="Save" />
</form>
</div>

<div>
[<a href="phpPgAdmin/">Run phpPgAdmin</a>]
</div>

<div class="pg_service_conf">
<form action="<?php pe($_SERVER['PHP_SELF'] . '?autodetect') ?>" method="post">
<pre>
'pg_service.conf' location <input type="text" name="_pg_service_file" value="<?php pe($conf['servers'][0]['_pg_service_file']) ?>" />
</pre>
<input type="submit" value="Autodetect parameters from pg_service.conf" />
</form>
</div>

</body>
</html>
