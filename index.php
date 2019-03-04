<style type="text/css">
	html { background-color: #264348; text-align: center; margin: 0 auto;}
</style>
<?php
$start = time(); set_time_limit(3600); error_reporting(2); $site= $_GET['site'];

include_once 'vendor/autoload.php';

$all_files = array_diff(scandir('versions'), array('..','.') );

$versions_with_hashes = array();

$files_with_hashes = array();

$points=array();

//coloring the response
function colorize($text , $status){
	if ($status >=200 and $status < 300) {
		//green if status == 200 | red if other 
		return '<span style="text-align:center; margin:0 auto; color:green;" >'.$text.'</span><br>';
	}else{return '<span style="text-align:center; margin:0 auto; color:#8F1D21;" >'.$text.'</span><br>';}
}

function progress($progress =22 , $message ='lol message'){
echo
"
    <script language='javascript'>
    document.getElementById('progress').innerHTML='<div style=\'width:$progress%;background-image:url(pbar-ani.gif);\'>&nbsp;</div>';
    document.getElementById('information').innerHTML='$message';
    </script>
";
}

//initiate the progress once
function init_progress(){
 // Progress bar holder
	echo '<div id="progress" style="width:500px;border:1px solid #ccc; margin: 0 auto;"></div>';
// Progress information
	echo '<div id="information" style="width:500px margin:0 auto;"></div>';
}

function form(){
echo 
'
<form style="text-align:center;width: 555px;margin: 0 auto;background-color: white;padding: 22px;margin-top: 28px;">
<h4 style="text-align:center">wordpress version detector - <b>the hard way</b> </h4>
	<input type="url" size="65" name="site" required/>
	<input type="submit" name="">
	<br> 
	<br> developed by:
	<a href=\'https://fb.com/ahmedserial\' target=\'_black\'>ahmed hamada</a>
</form>
';
}

if (!isset($site)) {
	form();	exit();
}


//site not well formatted
if (!filter_var($site, FILTER_VALIDATE_URL)) {
	form();	exit('<h3 style="text-align:center; color:red">enter site only </h3>');
}


//add / if not exist 
if (!substr($site, -1) !== '/') $site=$site.'/';

foreach ($all_files as $index => $file_from_directory) {
	
	include_once "versions/$file_from_directory";
	foreach ($filehashes as $filename => $hash) {
		$files_with_hashes[$filename][]=$hash;
		$versions_with_hashes[$file_from_directory][]=$hash;
	}
}
	// var_dump($files_with_hashes);
	// var_dump($versions_with_hashes); // version[verion]  =  array(hash)


function add_points($response_hash){
	global $all_files;
	global $versions_with_hashes;
	global $points;
	
	foreach ($all_files as $key => $files) {
		if (in_array($response_hash, $versions_with_hashes[$files])){
			$points[$files]++;
		}
	}
}

function print_points()
{
	global $start;
	global $points;

	//sort points decending
	arsort($points);
	echo "<h1 align='center' style='width:500px; margin:50 auto; background-color:dimgrey;'>the result</h1>";
	$x=0;
	foreach ($points as $wp_version => $value) {
		
		$wp_version = str_replace('.php', '', $wp_version);
		$wp_version = str_replace('hashes-', '', $wp_version);

		echo " <center style='background-color:white;width: 520px;margin: 0 auto;'> wordpress ". $wp_version." : $value points</center><br>";
		$x++;
		if ($x>=12) {break;}
	}
	//if no result
	if (count($points) <= 1) {
		echo "<h3 style=text-align:center;>are you sure it is wordpress !! . . . no result :( </h3>";
	}
	echo '<h3 style=text-align:center> script time: '.
	(time() - $start) . ' seconds<br>';
	
}

init_progress();

$reqests_number=500;
$request_counter=0;

//our requests - version 4 files
foreach ($files_with_hashes as $path => $value) {
	$request_counter++;
	//request js files only
	if (stripos($path, '.js') == true OR stripos($path, '.txt') ) {

		if ($request_counter >= $reqests_number) {break;}
		
		$response = Requests::get($site.$path);
		$hash_the_response = md5($response->body);
		add_points($hash_the_response);
		
		$persentage = $request_counter / $reqests_number * 100;
		$message = colorize($path." -- $persentage %" , $response->status_code);
		progress($persentage , $message);
	}

	ob_flush();
	flush();
}
print_points();
?>