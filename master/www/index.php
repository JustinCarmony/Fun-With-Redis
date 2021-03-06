<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/25/12
 * Time: 11:21 AM
 * To change this template use File | Settings | File Templates.
 */

?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Fun with Redis - Master Control Center of Doom</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">

	<!-- Le styles -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<style>
		body {
			padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
		}
	</style>
	<link href="/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="/css/style.css" rel="stylesheet">

	<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- Le fav and touch icons -->
	<link rel="shortcut icon" href="/ico/favicon.ico">
	<link rel="apple-touch-icon-precomposed" sizes="144x144" href="/ico/apple-touch-icon-144-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="114x114" href="/ico/apple-touch-icon-114-precomposed.png">
	<link rel="apple-touch-icon-precomposed" sizes="72x72" href="/ico/apple-touch-icon-72-precomposed.png">
	<link rel="apple-touch-icon-precomposed" href="/ico/apple-touch-icon-57-precomposed.png">
</head>

<body>

<div class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<a class="brand" href="#">Fun with Redis - Ludicrous Speed Style</a>
			&nbsp;&nbsp;&nbsp;&nbsp;<a class="brand" href="#" style="floatL:right;" onclick="HardReset(); return false;">Hard Reset</a>&nbsp;&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;&nbsp;&nbsp;<a class="brand" href="#" style="floatL:right;" onclick="MasterReset(); return false;">Master Reset</a>&nbsp;&nbsp;&nbsp;&nbsp;
		</div>
	</div>
</div>

<div class="container">
	<div id="main">
		<div class="row">
			<div class="span8 offset2">
				<h2>Current Mode Information</h2>
				<div class="well">
					<div id="lblModeStatus">
						<h1 style="text-align: center;">I'm Idling... Please touch my buttons...</h1>
					</div>
				</div>
			</div>
		</div>
		<hr />
		<div class="row">
			<div class="span12">
				<h2>Redis CPU Usage: <span id="lblCpuUsage">-</span>%</h2>
				<div class="progress progress-info" style="height:30px;">
					<div id="barCpuUsage" class="bar" style="width:0%; height:30px;"></div>
				</div>
			</div>
		</div>
		<hr/>
		<div class="row">
			<div class="span6">
				<h2>Minion Controls</h2>
				<hr/>
				<h3>Active Work Force</h3>
				<div id="btnGrpWorkForce" class="btn-group" data-toggle="buttons-radio">
					<button data-force="0" class="btn active btn-primary">0%</button>
					<button data-force="10" class="btn">10%</button>
					<button data-force="20" class="btn">20%</button>
					<button data-force="30" class="btn">30%</button>
					<button data-force="40" class="btn">40%</button>
					<button data-force="50" class="btn">50%</button>
					<button data-force="60" class="btn">60%</button>
					<button data-force="70" class="btn">70%</button>
					<button data-force="80" class="btn">80%</button>
					<button data-force="90" class="btn">90%</button>
					<button data-force="100" class="btn">100%</button>
				</div>
				<hr/>
				<h3>Mode</h3>
				<div id="btnGrpMode" class="btn-group" data-toggle="buttons-radio">
					<button data-mode="idle" class="btn active btn-primary">Idle</button>
					<button data-mode="increment" class="btn">Incr</button>
					<button data-mode="random_number" class="btn">Rand Num</button>
					<button data-mode="md5_gen" class="btn">MD5 Gen</button>
					<button data-mode="rand_read" class="btn">Rand Read</button>
					<button data-mode="rand_write" class="btn">Rand Write</button>
					<button data-mode="bench" class="btn">Bench</button>
				</div>
				<hr/>
				<h3>Pipeline</h3>
				<div class="row">
					<div class="span2">
						<div id="btnGrpPipeline" class="btn-group" data-toggle="buttons-radio">
							<button data-pipeline="off" class="btn active btn-primary">Disabled</button>
							<button data-pipeline="on" class="btn">Enabled</button>
						</div>
					</div>
					<div class="span4"><form class="form-inline"><label><strong># of Commands</strong></label> <input id="txtPipelineCount" type="text" class="span1" value="100"></form></div>
				</div>
			</div>
			<div class="span6">
				<div class="well">
					<h1>A Few Fun Stats</h1>
					<h3>Servers Available: <span id="lblServersCount">0</span></h3>
					<h3>Minions Available: <span id="lblMinionsCount">0</span></h3>
					<h3>Minions Active: <span id="lblMinionsActive">0</span></h3>
                    <h3>Total Keys: <span id="lblTotalKeys">0</span></h3>
					<h3>Total Commands Executed: <span id="lblTotalCommandsExecuted">0</span></h3>
					<h3>Max Recorded CpS: <span id="lblMaxRpS">0</span></h3>
					<h3>Commands Per Second:</h3>
					<h1 id="lblCmdPerSec" style="text-align: center; font-size:80px; padding:20px 0;">0</h1>

				</div>
			</div>
		</div>
		<div class="row">
			<div id="divServerList" class="span12">

			</div>
		</div>
	</div>
</div> <!-- /container -->

<!-- Le javascript
	================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="/js/jquery.js"></script>
<!-- <script src="/js/bootstrap-transition.js"></script> -->
<script src="/js/bootstrap-alert.js"></script>
<script src="/js/bootstrap-modal.js"></script>
<script src="/js/bootstrap-dropdown.js"></script>
<script src="/js/bootstrap-scrollspy.js"></script>
<script src="/js/bootstrap-tab.js"></script>
<script src="/js/bootstrap-tooltip.js"></script>
<script src="/js/bootstrap-popover.js"></script>
<script src="/js/bootstrap-button.js"></script>
<script src="/js/bootstrap-collapse.js"></script>
<script src="/js/bootstrap-carousel.js"></script>
<script src="/js/bootstrap-typeahead.js"></script>

<script>
	function Setup()
	{
		/* Buttons */

		// Work Force Group
		$('#btnGrpWorkForce .btn').click(function(){
			$('#btnGrpWorkForce .btn').removeClass('btn-primary');
			console.log("Start Click");
			var btn = this;
			$.post('cmd.php', {cmd: "set", args:["system.workforce", $(this).data('force')]}, function(){
				console.log("End Click");
				$(btn).addClass('btn-primary');
				PollMinions(true);
			});
		});

		// Mode
		$('#btnGrpMode .btn').click(function(){
			$('#btnGrpMode .btn').removeClass('btn-primary');
			console.log("Start Click");
			var btn = this;
			$.post('mode.php', {mode: $(this).data('mode') }, function(){
				console.log("End Click");
				$(btn).addClass('btn-primary');
				PollMinions(true);
			});
		});

		// Pipeline
		$('#btnGrpPipeline .btn').click(function(){
			$('#btnGrpPipeline .btn').removeClass('btn-primary');
			console.log("Start Click");
			var btn = this;
			$.post('cmd.php', {cmd: "set", args:["system.pipeline", $(this).data('pipeline')]}, function(){
				console.log("End Click");
				$(btn).addClass('btn-primary');
				PollMinions(true);
			});

			$.post('cmd.php', {cmd: "set", args:["system.pipeline_count", $('#txtPipelineCount').val()]}, function(){
				console.log("End Click");
				$(btn).addClass('btn-primary');
				PollMinions(true);
			});
		});

		$('#txtPipelineCount').change(function(){
			$.post('cmd.php', {cmd: "set", args:["system.pipeline_count", $(this).val()]}, function(){
				PollMinions(true);
			});
		});
	}

	function LoadData()
	{

	}

	function ResetPanel()
	{
		// Reset to Idle
		$('#btnGrpWorkForce button[data-force="0"]').click();
		$('#btnGrpMode button[data-mode="idle"]').click();
		$('#btnGrpPipeline button[data-pipeline="off"]').click();
	}

	function PollStats()
	{
		$.ajax({
			url: 'stats.php',
			dataType: 'json',
			success: function(data)
			{
				$.each(data, function(k,v){
					$('#' + k).html(v.toString());
				});
				setTimeout("PollStats();", 1000);
			},
			error: function()
			{
				setTimeout("PollStats();", 5000);
			}
		});
	}

	function PollCpu()
	{
		$.ajax({
			url: 'cpu.php',
			dataType: 'json',
			success: function(data)
			{
				$.each(data, function(k,v){
					$('#' + k).html(v.toString());
				});
				var cpu = parseFloat(data.lblCpuUsage) + 0.5;
				if(cpu > 100)
				{
					cpu = 100;
				}
				// Set CPU Bar
				$("#barCpuUsage").css('width', cpu.toString() + '%');
				setTimeout("PollCpu();", 10);
			},
			error: function()
			{
				setTimeout("PollCpu();", 3000);
			}
		});
	}

	var minion_data = null;

	function PollMinions(only_once)
	{
		$.ajax({
			url: 'minions.php',
			dataType: 'json',
			success: function(data)
			{
				minion_data = data;
				if(!only_once)
				{
					setTimeout("PollMinions(false);", 3000);
				}
				UpdateMinions();
			},
			error: function()
			{
				if(!only_once)
				{
					setTimeout("PollMinions(false);", 6000);
				}
			}
		});
	}

	function UpdateMinions()
	{
		var minion_list = [];

		$.each(minion_data.servers, function(server_k, server_v){
			if($('#' + server_k).length == 0)
			{
				// Get First Minion
				var server_data;
				for (var i in server_v) {
					if (server_v.hasOwnProperty(i) && typeof(i) !== 'function') {
						server_data = server_v[i];
						break;
					}
				}

				//console.log(server_data);
				if(!server_data)
				{
					return;
				}

				var server_html = '<div id="' + server_k + '" class="well serverListing" style="width:200px; margin-right:20px; float:left;"><h3>Server</h3><p class="info">'
					+ '<strong>IP:</strong> '+ server_data.ip + '<br /> <strong>Hostname: </strong> ' + server_data.hostname + '</p><div class="divServerMinionList"></div></div>'
				$('#divServerList').append(server_html);
			}

			// Loop Through Minions
			$.each(server_v, function(minion_k, minion_v){
				minion_list.push(minion_k);
				if($('#' + minion_v.server_id + ' #' + minion_k).length == 0)
				{
					// Remove any other conflicting minions assigned to other servers
					$('#' + minion_k).remove();
					var minion_html = '<div id="' + minion_k + '" class="minionListing alert alert-info"><h3>Minion #  <span class="minion_id">' + minion_v.minion_id + '</span></h3>'
						+ '<strong>Status: </strong> <span class="status"></span><br/>'
						+ '<strong>Internal ID: </strong> <span class="internal_id"></span><br/>'
						+ '<strong>Latency: </strong> <span class="latency_ms"></span> ms<br/>';
					$('#' + server_k + ' .divServerMinionList').append(minion_html);
				}
				var status = '';
				if(minion_v.working)
				{
					status = 'Working';
					$('#' + minion_k).removeClass('alert-info');
					$('#' + minion_k).addClass('alert-success');
				}
				else
				{
					status = '<em>Waiting</em>';
					$('#' + minion_k).removeClass('alert-success');
					$('#' + minion_k).addClass('alert-info');
				}
				
				$('#' + minion_k + ' .status').html(status);
				$('#' + minion_k + ' .internal_id').html(minion_v.internal_id);
				$('#' + minion_k + ' .latency_ms').html(minion_v.latency_ms);
			});
		});

		$('#lblServersCount').html($('.serverListing').length);
	}

	function HardReset()
	{
		$.post('cmd.php', {cmd: "incr", args:["system.instance"]}, function(){
			$.post('cmd.php', {cmd: "incr", args:["reboot.minion"]}, function(){
				window.location.reload();
			});
		});
	}

	function MasterReset()
	{
		$.post('cmd.php', {cmd: "incr", args:["reboot.master"]}, function(){
			HardReset();
		});
	}

	$(document).ready(function(){
		Setup();
		LoadData();
		PollCpu();
		PollStats();
		PollMinions();
		ResetPanel();
	});
</script>

</body>
</html>
