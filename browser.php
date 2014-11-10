<!DOCTYPE html>
<html>
<head>
	<title>pSimpleFileBrowser explorer</title>
	<style>	
	div.nav {
		color:black;font-family:Trebuchet MS,Tahoma,Helvetica,Verdana,Arial;background-color: #EEEEFF;
		}
	div.file {
		color:black;font-family:Trebuchet MS,Tahoma,Helvetica,Verdana,Arial;background-color: #BBBBFF;
		}
	div.size {
		font-family:Trebuchet MS,Tahoma,Helvetica,Verdana,Arial;background-color: #CCCCFF;
		}
	div.date {
		font-family:Trebuchet MS,Tahoma,Helvetica,Verdana,Arial;background-color: #DDDDFF;
		}

	a:link {color:black;text-decoration:none;}
	a:visited {color:black;text-decoration:none;}
	a:hover {color:blue;text-decoration:none;}
	a:active {color:black;text-decoration:none;}
	</style>
</head>
<body>

<?php



error_reporting(E_ERROR | E_PARSE);

// this is where we catch variables sent to this page.
$dir = $_REQUEST['dir'];

// fix stuff & block directory traversal
if (strstr($dir,"..") OR ($dir == "/")) {
  $dir = "";
  }

// learn the world  
$thisfilelocation = $_SERVER['PHP_SELF'];
$thisfilename = pathinfo($thisfilelocation)['basename'];
$thisfilepath = str_replace($thisfilename, "",$thisfilelocation);

$workdir = getcwd(); // no trailing '/'
$webdir = $_SERVER['SERVER_NAME'].$thisfilepath;


/* debug  
print "<pre>";
print_r(get_defined_vars());
print "</pre>";
 */

// This merge together the base directory and the user's requested folder.
$fulldir = $workdir.$dir;

// path tree
if ($handle = opendir($fulldir)) {
  if ($dir) {
    $i = 1;
    $dirarray = explode("/",$dir);
    foreach ($dirarray as &$folder) {
      if (!$folder) {
		$folder = "root"; } // human name to root
      print "<div class='nav'>";
      for($s = 0; $s < $i; $s++) { print "&nbsp;"; } // sposta il testo verso destra in base alla sottocartella
      print '<a href="?dir=';
      for($s = 0; $s < $i; $s++) {
        if ($s) { print $dirarray[$s]; }
        if ($dirarray[$s] != $folder) { print "/"; } // non mette la / se gia alla fine del path
        }
      print '">';
      print "[$folder]";
      print "</a></div>";
      $i++;
      }
    }
	else { 
		print "<div class='nav'>&nbsp;[root]</div>"; 
		}
		
		
  print "<hr>";
  // directory list with scandir
  $dir_path = $fulldir."/";
  $exclude_list = array(".", "..",);
  $directories = array_diff(scandir($dir_path), $exclude_list);
  
  print "<table>";
  
  
  foreach($directories as $entry) {
    if(is_dir($dir_path.$entry)) {
      print "<tr>";
      print '<td><div class="file"><a href="./?dir='.$dir."/".$entry.'">['.substr($entry,0,150).']</a></div></td>';
	  print "<td><div class='size'>".human_filesize(get_file($dir_path.$entry)['size'])."</div></td>";
	  print "<td><div class='date'>".get_file($dir_path.$entry)['updated']."</div></td>";
      print "</tr>";
    }
  }
    
    
  foreach($directories as $entry) {
    if(is_file($dir_path.$entry)) {
      print "<tr>";
	  
	  print "<td><div class='file'>";
		print "<a href='//".get_file($dir_path.$entry)['link']."'>";
		print get_file($dir_path.$entry)['name'];
	  print "</a></div></td>";
	  print "<td><div class='size'>".human_filesize(get_file($dir_path.$entry)['size'])."</div></td>";
	  print "<td><div class='date'>".get_file($dir_path.$entry)['updated']."</div></td>";
 
      print "</tr>";
    }
  }
  
    
  print "</table><br />";

  }
  
function get_file($entry) {
	global $workdir, $webdir, $dir;
	if (file_exists($entry)) {
		$name = pathinfo($entry)['basename'];
		$folder = pathinfo($entry)['dirname'];
		$fixed_dir = substr($dir,1)."/"; // move '/' from start to end
		$link = $webdir.$fixed_dir.$name;
		$size = filesize($entry);
		$updated = date ("d/m/Y", filemtime($dir_path.$entry));
		
		return compact('name','folder','link','size','updated');
		}
		else {
			return false;
		}
}
 

function human_filesize($bytes, $decimals = 2) {
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
} 

/* pascal brax 2014 */
  
?>
</body>
</html>
