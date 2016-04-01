<!DOCTYPE html>
<html>
<head>
	<title>pSimpleFileBrowser</title>
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

if (strstr($dir, "..") OR ($dir == "/")) {
    $dir = "";
}

// learn the world

$thisfilelocation = $_SERVER['PHP_SELF'];
$thisfilename = pathinfo($thisfilelocation) ['basename'];
$thisfilepath = str_replace($thisfilename, "", $thisfilelocation);
$workdir = getcwd(); // no trailing '/'
$webdir = $_SERVER['SERVER_NAME'] . $thisfilepath;

// This merges together the base directory and the user's requested folder.

$fulldir = $workdir . $dir;

// hide files that contain this string

//$hidefiles = "browser.php";
$hidefiles = $thisfilename;


// path tree

if ($handle = opendir($fulldir)) {
    if ($dir) {
        $i = 1;
        $dirarray = explode("/", $dir);
        print "<div class='nav'>";
        foreach($dirarray as & $folder) {
            if (!$folder) {
                $folder = "root";
            } // human name to root
            print '<a href="?dir=';
            for ($s = 0; $s < $i; $s++) {
                if ($s) {
                    print $dirarray[$s];
                }

                if ($dirarray[$s] != $folder) {
                    print "/";
                } // add '/' in the right places for the GET variables
            }

            print '">';
            print '<img width="12" height="12" src="'; // start img tag
            print add_icon("nav"); // insert image data
            print '" />&nbsp;'; // close img tag (and add a space)
            print "$folder";
            print "</a>";
            $i++;
        }

        print "</div>";
    }
    else {
        print '<div class="nav"><img alt="[dir]" width="12" height="12" src="' . add_icon("nav") . '" />&nbsp;root</a></div>';
    }

    print "<hr>";

    // directory list with scandir

    $dir_path = $fulldir . "/";
    $exclude_list = array(
        ".",
        "..",
    );
    $directories = array_diff(scandir($dir_path) , $exclude_list);
    print "\n<table>"; // start filetable
    foreach($directories as $entry) {
        if (is_dir($dir_path . $entry)) {
            print "<tr>";
            print '
                <td>
                    <div class="file">
                        <img width="12" height="12" src="' . add_icon("nav") . '" />
                        <a href="' . $thisfilename . '?dir=' . $dir . "/" . $entry . '">' . substr($entry, 0, 150) . '</a>
                    </div>
                </td>';
            print "<td><div class='size'>" . human_filesize(get_file($dir_path . $entry) ['size']) . "</div></td>";
            print "<td><div class='date'>" . get_file($dir_path . $entry) ['updated'] . "</div></td>";
            print "</tr>\n";
        }
    }

    foreach($directories as $entry) {
        if (is_file($dir_path . $entry) AND !strpos(" ".$entry, $hidefiles) ) {
            print "<tr>";
            print "<td><div class='file'>";
            print '<img width="12" height="12" src="' . add_icon("empty") . '" />&nbsp;' . "<a href='//" . get_file($dir_path . $entry) ['link'] . "'>";
            print get_file($dir_path . $entry) ['name'];
            print "</a> </div> </td>";
            print "<td><div class='size'>" . human_filesize(get_file($dir_path . $entry) ['size']) . "</div></td>";
            print "<td><div class='date'>" . get_file($dir_path . $entry) ['updated'] . "</div></td>";
            print "</tr>\n";
        }
    }

    print "</table><br />"; // end filetable
}

function get_file($entry)
{
    global $workdir, $webdir, $dir;
    if (file_exists($entry) ) {
        $name = pathinfo($entry) ['basename'];
        $folder = pathinfo($entry) ['dirname'];
        $fixed_dir = substr($dir, 1) . "/"; // move '/' from start to end
        $link = $webdir . $fixed_dir . $name;
        $size = filesize($entry);
        $updated = date("d/m/Y", filemtime($dir_path . $entry));
        return compact('name', 'folder', 'link', 'size', 'updated');
    }
    else {
        return false;
    }
}

function add_icon($type)
{
    if ($type == "nav") {
        $icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHBJREFUeNqU0TEKwCAUA9D2Sp5AXUQQ3cSbewgHN8Ep8tdqaRr44+NDcgO4fkXA87TWcM6BBtZajDHO6A1IBIUQQANJaw0xRtBgQwyQ1FqhlAIFeu/w3nMftra+WkopcS3NOZFz5nYwxqCUclx6CTAAwWgxaW7qSDsAAAAASUVORK5CYII%3D";
    }

    if ($type == "dir") {
        $icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAAAvUlEQVR4nI2PvQrCMBSFT+xNDehziJOjoCD4AK7OPpuzq7tQQUXcLbQ+govWmgSTOChSTAv5xvPDPZfhi9zCoYKYgKEG9kyYAwAx3v9EuRvVZT+FYhO5znDVGKjyOM5B1kawZR5UcI6BjGnBFemfZQG0aktkDIMrL0EXlOIgpXnwJKU5SMoYeN09M8+6nibaMUiqGHkWe2Z/tvC0dL0EScUxmPY8015PniYVB2nNYW/noB+0FiCtCYfE39vEG1EaRqExGIWPAAAAAElFTkSuQmCC";
    }

    if ($type == "file") {
        $icon = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAYAAABWdVznAAABH0lEQVR4nI3OMU7DQBCF4X/tNZAGKm5Ag8QZOAA93CBNWrpcAYmWhpYLUKRJwQGooQYpSAGEQMHeXa+9MxRGCDsUvGak2f1mxgBM5kvlj5wf7rK1mZu1h/FsoY+vXkVVYyu6fK91PFvoZL7Ul7fQG5YBhBAZ2YwkigGMQrnyTA62Ob15onTND7IAPgQEpU7SfY6J1Yfj4blkr4Djyzv6wNXYjZy6EcAQRQgucHG7AANmRB9UlcO3qbvPGLLCcHayTytCm5Tp9f0QeFwUbAaKIc8MOyMLQGiEqvJDEAiNkGVd0wD6XauYKKvQB845XGyx+bpwdUtVugHwNT4Jhf6e3cU3Ce+GG3x3UrIQG6H08dsoRW4Jvu6D8tNxNL3iP/kC9mepySQNpKwAAAAASUVORK5CYII%3D";
    }

    if ($type == "empty") {
        $icon = "data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7";
    }

    return $icon;
}

function human_filesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

/* pascal brax 2014 */
?>
</body>
</html>
