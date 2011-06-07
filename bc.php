<?php
class BaseCampTimeReporter {
	var $projectList = array();
	var $userId = 0;
	var $username="";
	var $password="";
	var $baseUrl = "https://ceegees.basecamphq.com";
	var $users;

	function getBaseCampData($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_USERPWD,"".$this->username.":".$this->password);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		$content = curl_exec($ch);
		curl_close($ch);
		return $content;
	}
	function initializeBCdata($u,$p) {
		$this->username = $u;
		$this->password = $p;
		$url = $this->baseUrl."/projects.xml";
		$projects = $this->getBaseCampData($url);
		$projects = new SimpleXMLElement($projects);
		foreach ($projects->children() as $proj) {
			$this->projectList["".$proj->id]  = "".$proj->name;
		}
		$xmlstr = $this->getBaseCampData($this->baseUrl."/me.xml");
		$xml = new SimpleXMLElement($xmlstr);
		$this->userId = $xml->id;
	}

	function processTimeEntries($from=0,$to=0) {
		$url = $this->baseUrl."/time_entries/report.xml?from=$from&to=$to&subject_id=$this->userId";
		$times = $this->getBaseCampData($url);
		$times = new SimpleXMLElement($times);
		$dates = array();
		$projectsUsed = array();
		$total = 0;
		foreach ($times->children() as $tm) {
			$dt = "".$tm->date;
			$projName = $this->projectList["".$tm->{'project-id'}];
			if(!isset($dates[$dt]) ) {
				$dates[$dt] = array("total"=>0, "projects"=>array());
			}
			$dates[$dt]['total'] += "".$tm->hours;
			$disp = "";
			if(!isset($dates[$dt]['projects'][$projName])) {
				$dates[$dt]['projects'][$projName] = "". round("".$tm->hours,1);
			} else {
				$dates[$dt]['projects'][$projName] .= "+".round("".$tm->hours,1);
			}
			$total += "".$tm->hours;
			if (!isset($projectsUsed[$projName])) {
				$projectsUsed[$projName]  = 0;
			}
			$projectsUsed[$projName] += "".$tm->hours;
		}
		ksort($dates);
		return array("projs" => $projectsUsed , "dates" => $dates ,"total"=> $total,"users"=> $this->users);
	}

}
?>
<?php 


	$data =  isset($_POST["data"]) ? true: false;
	$user =  isset($_POST['user']) ? $_POST['user'] : "";
	$pass =  isset($_POST['pass']) ? $_POST['pass'] : "";
	$end =   isset($_POST['to'])   ? $_POST["to"] :date("Ynj");
	$start = isset($_POST['from']) ? $_POST["from"] : date("Ynj",  time() - (31 * 24 * 60 * 60));
	
?>
<!DOCTYPE
unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>The Base camp time reporting made easy</title>
<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"> </script>
<link rel="stylesheet" type="text/css"
	href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" />
<script type="text/javascript">
$(function() {
	$.datepicker.setDefaults({ dateFormat: 'yymmdd' });
	$("#datepicker").datepicker();
	$("#datepicker1").datepicker();
	$("input:submit").button();
});
</script>

<style type="text/css">
.ui-widget { font-family: Verdana,Arial,sans-serif/*{ffDefault}*/; font-size: 0.9em/*{fsDefault}*/; }
td th {font-size:0.7em}
</style>
</head>
<body>
	<br/>
	Please enter Your base camp credentials , We wont steal them .. :)
. <br />
This code will be open sourced , so you can always download this from Git Hub and host it on your server 
<br />
<div class="ui-widget ui-corner-all  ui-widget-content"
	style="padding: 10px">
<form action="" method="post">
<table>
	<tr>
		<td>User Name :</td>
		<td><input class="ui-corner-all" type="text" name="user"
			value="<?php echo $user;?>" /></td>
	
		<td>Password :</td>
		<td><input class="ui-corner-all" type="password" name="pass"
			value="<?php echo $pass;?>" /></td>
			</tr><tr>
		<td>Start Date (YYYYMMDD)</td>
		<td><input class="ui-corner-all" id="datepicker" type="text"
			name="from" value="<?php echo $start;?>" /></td>
		<td>End Date (YYYYMMDD)</td>
		<td><input class="ui-corner-all" id="datepicker1" type="text"
			name="to" value="<?php echo  $end;?>" /></td>
		<td><input type="submit" value="generate Report" /></td>
	</tr>
</table>
</form>	
<?php 
	if ($user != "" && $pass != "") {
		$bc = new BaseCampTimeReporter();
		$bc->initializeBCdata($user,$pass);
		$res = $bc->processTimeEntries($start,$end);
		echo "<h2>Hours registered by ".ucfirst($user)."</h2>";
		?>
<table border="1" id="results"> <thead>
	<tr>
		<th>Date</th>
		<th>total [<?php echo $res['total']; ?>] </th>
		<?php
		$dts = $res['dates'];
		foreach ($res['projs'] as  $key=>$proj) {
			echo "<th>".$key."  [".$proj."]  </th>";
		}
		?>
		</tr></thead><tbody>
		<?php 
		foreach ($dts as $key => $val) {
			$day = date("D",strtotime($key));
			$style ="";
			if ($day == "Sun" || $day == "Sat") {
				$style = 'style="background:#6D7B8D;color:white"';
			}
			echo"<tr $style>";
			echo "<td>$key - $day </td><td>".$val['total']."</td>";
			foreach ($res['projs'] as  $proj=>$val2) {
				echo "<td>";
				if (isset($val['projects'][$proj]) ){
					echo $val['projects'][$proj];
				} else {
					echo "&nbsp;";
				}
				echo "</td>";
			}
			echo "</tr>";
		}
		?>
	</tbody>
</table>
<?php	} ?> 
</div>
</body>
</html>