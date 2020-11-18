<?php
	// Jan Zikan, Czech Repulic, https://gist.github.com/janzikan
	include('../resize_image.php');

// Starting clock time in seconds 
$start_time = microtime(true); 
$a=1; 

	$t = array(); // thumbnails
	$f = array(); // Full Sized
	$p = array(); // Page Sized
	$i = array(); // Individual Items
	$items = array(); // Subset, individual items from a single page
	
	$dir    = './'; // Current Directory
	$files = scandir($dir); // Scan for files in directory, alphabetically sorted

	$f = array_filter($files, "full"); // Calls full() on file list array, assumes full size files are correctly named

	// Step through list of full sized files and create page and thumnb files
	foreach($f as $exfull) {
		getelement($exfull); // filename is parsed for page number and file type
		$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
		print_r("exfull: " . $exfull . " expage: " . $expage . "\n");
		// Future: resize recognizing double width pages
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		resizeImage($exfull, $expage, 800, 0, 100); // Resize full page to page filename. 
		$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
		print_r("exfull: " . $exfull . " exthumb: " . $exthumb . "\n");
		// Future: resize recognizing double width pages
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		resizeImage($exfull, $exthumb, 200, 0, 100); // Resize full page to thumbnail filename.
	}
	
	// As above, rescans including page and thumb image files.
	$dir    = './';
	$files = scandir($dir);

	// Calls appropriate function for each additional file type.
	// Assumes item files have been created manually
	$t = array_filter($files, "thumb");
	$p = array_filter($files, "pic");
	$i = array_filter($files, "item");
	
	// Figure out how buig the exhibit is, but makes some assumptions that may not be valid
	// First assumes that most frames are 16 pages in size, or close enough. 
	// This will fail if there are enough double pages to have a total number of frames greater than pages/16 would account for.
	// Future: Count missing pages so double pages are counted as two instead of one.
	// Future: potentially pull in frame layout from a data file.
	$numframes = (count($f) + 1) % 16;
	$numpages = count($p);
	
	// Creat variables for use when parsing a file name.
	$atype = ""; // Array type; f, p, t, i
	$anum = 0; // Page number
	$enum = 0; // Item number on page
	$ftype = ""; // File type (.jpg, .png, etc.)

	/*
	print_r("Thumbs: \n");
	print_r($t);
	print_r("Pages: \n");
	print_r($p);
	print_r("Full: \n");
	print_r($f);
	print_r("Items: \n");
	print_r($i);
	*/


// Now, build the frame page, or pages, using the thumb array and the frames count.
for ($iteration = 1; $iteration <= $numframes; $iteration++) {
	$framefile = fopen("exframe" . sprintf("%'.02d", $iteration) . ".html", "w") or die("Unable to open frame file!");
	buildframe($framefile, $iteration, $numframes);
}

// And then build the pages the frame links point to.
for ($iteration = 1; $iteration <= $numpages; $iteration++) {
	$pagefile = fopen("expage" . sprintf("%'.03d", $iteration) . ".html", "w") or die("Unable to open frame file!");
	buildpage($pagefile, $iteration, $numpages, $i);
}

function getitems($pagenum)
{
	// Globals are from the main body of the script, so they don't have to be passed individually.
	global $i; 
	global $items;
	global $atype;
	global $anum;
	global $enum;
	global $ftype;

	// returns all items that match the page number
	$items = null;
	// print_r($i);
	foreach ($i as $tempvar) {
		getelement($tempvar);
		if ($anum === $pagenum) {
			$items[$enum] = $tempvar;
		}
	}
	return $items;

}

function getelement($a)
{
	 // Globals are from the main body of the script, so they don't have to be passed individually.
	global $atype;
	global $anum;
	global $enum;
	global $ftype;

	// Gets a single element from an array and parses it for info
	if ($a <> null) {
		$atype = $a[0];
		$anum = intval(substr($a, 1, 3));
		$enum = intval(substr($a, 5, 2));
		$dotpos = strlen($a) - 4;
		$ftype = substr($a, $dotpos, 4);
	}
}

function thumb($var)
{
    // returns entry where the first character is 't'
	$first = $var[0];
	if ($first === 't')  {
		return $var;
	}
    return false;
}

function pic($var)
{
    // returns entry where the first character is 'p'
	$first = $var[0];
	if ($first === 'p')  {
		return $var;
	}
    return false;
}

function full($var)
{
    // returns entry where the first character is 'f'
	$first = $var[0];
	if ($first === 'f')  {
		return $var;
	}
    return false;
}

function item($var)
{
    // returns entry where the first character is 'i'
	$first = $var[0];
	if ($first === 'i')  {
		return $var;
	}
    return false;
}

function buildframe($ffile, $framenum, $numframes)
{
	// Globals are from the main body of the script, so they don't have to be passed individually.
	global $t;
	global $p;

	global $atype;
	global $anum;
	global $enum;
	global $ftype;
	
	// Initial page setup
	$titlefile = fopen("extitle.txt", "r") or die("Unable to open title file!");
	$extitle = fgets($titlefile);
	$title = $extitle . " " . $framenum;
	$index = ($framenum - 1) * 16;
	// print_r ("Index: " . $index . "\n");
	$txt = "<!DOCTYPE html>\n<html>\n\n<head>\n";
	fwrite ($ffile, $txt);
	$txt = "<Title>" . $extitle . "</title>\n";
	fwrite ($ffile, $txt);
	$txt = "<link rel=\"stylesheet\" href=\"../exhibit.css\" />";
	fwrite ($ffile, $txt);
	$txt = "</head>\n<body>\n";
	fwrite ($ffile, $txt);
	// Navigation header table
	$txt = "\t<div align=\"center\">\n\t<center>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t<table border=\"0\" width=\"100%\">\n\t\t\t<tr>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">";
	fwrite ($ffile, $txt);
	if ($framenum === 1)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum - 1)) . ".html\"><button class=\"button exbutton\">Previous Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">\n\t\t\t\t\t<a href=\"../../exhibits.html\"><button class=\"button exbutton\">Back to Exhibits Index Page</button></a>\n\t\t\t\t</td>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">";
	fwrite ($ffile, $txt);
	if ($framenum === $numframes)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum + 1)) . ".html\"><button class=\"button exbutton\">Next Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	// Exhibit Title
	$txt = "\t\t\t<tr>\n\t\t\t\t<td align=\"center\">&nbsp;</td>\n\t\t\t\t<td align=\"center\">";
	fwrite ($ffile, $txt);
	fwrite ($ffile, $extitle);
	$txt = "\n\t\t\t\t</td>\n\t\t\t\t<td align=\"center\">&nbsp;</td>\n\t\t\t</tr>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t</table>\n\t\t</center>\n\t</div>\n";
	fwrite ($ffile, $txt);
	// End of navigation header table
	
	$txt = "\t<div align=\"center\">\n\t<center>\n";
	fwrite ($ffile, $txt);
	$txt = "\t<table border=\"0\" width=\"100\%\">\n\t<tbody>\n";
	fwrite ($ffile, $txt);
	
	$step = 1;
	foreach($p as &$value) {
		getelement($value);
		if ($step % 4 == 1) {
			$txt = "\t<tr>\n";
			fwrite ($ffile, $txt);
		}
		$txt = "\t\t<td align=\"center\">\n";
		fwrite ($ffile, $txt);
		if ($index + $step == $anum) {
			$txt = "\t\t\t<a href=\"expage" . (sprintf("%'.03d", $anum)) . ".html\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t<img border=\"2\" src=\"t" .  (sprintf("%'.03d", $anum)) . $ftype . "\" /></a>\n";
			fwrite ($ffile, $txt);
		}
		else {
			$txt = "\t\t\t\&nbsp;\">\n\t\t</td>\n";
			fwrite ($ffile, $txt);
		}
		if ($step % 4 == 0) {
			$txt = "\t</tr>\n";
			fwrite ($ffile, $txt);
		}
		$step++;
	}
	$txt = "\t</tbody>\n</table>\n</div>\n";
	fwrite ($ffile, $txt);
	
	// Navigation footer table
	$txt = "\t<div align=\"center\">\n\t<center>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t<table border=\"0\" width=\"100%\">\n\t\t\t<tr>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">";
	fwrite ($ffile, $txt);
	if ($framenum === 1)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . ($framenum - 1) . ".html\"></td>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">\n\t\t\t\t\t<a href=\"../../exhibits.html\"><button class=\"button exbutton\">Back to Exhibits Index Page</button></a>\n\t\t\t\t</td>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">";
	fwrite ($ffile, $txt);
	if ($framenum === $numframes)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . ($framenum + 1) . ".html\"></td>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t\t</tr>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t</table>\n\t\t</center>\n\t</div>\n";
	// Author blurb, update date/time
	fwrite ($ffile, $txt);
	$txt = "<font color=\"blue\"><small><b>Virtual Exhibit Script Authored by - <a href=\"mailto:billsey@seymourfamily.com.com\">Bill Seymour</a></b></small></font><br />\n";
	fwrite ($ffile, $txt);
	$txt = "<p align=\"right\"><strong><small>Last modified:";
	fwrite ($ffile, $txt);
	$txt = "<script language=\"JavaScript\">var testlast=document.lastModified;document.write(\" \"+testlast.substr(0,10));</script>\n";
	fwrite ($ffile, $txt);
	$txt = "</small></strong></p>\n";
	// End page
	fwrite ($ffile, $txt);
	$txt = "</body>\n</html>\n";
	fwrite ($ffile, $txt);
	
	fclose($ffile);
}

function buildpage($pfile, $pagenum, $numpages, $item) {
	global $items;
	global $i;

	// Initial page setup
	$titlefile = fopen("extitle.txt", "r") or die("Unable to open title file!");
	$extitle = fgets($titlefile);
	$title = $extitle . " Page " . $pagenum;
	$upframe = sprintf("%'.02d", ($pagenum - (($pagenum - 1) % 16)));
	print_r("pagenum: " . $pagenum . " upframe: " . $upframe . "\n");
	$txt = "<!DOCTYPE html>\n<html>\n\n<head>";
	fwrite ($pfile, $txt);
	$txt = "<Title>" . $title . "</title>\n";
	fwrite ($pfile, $txt);
	$txt = "<link rel=\"stylesheet\" href=\"../exhibit.css\" />";
	fwrite ($pfile, $txt);
	$txt = "</head>\n<body>\n";
	fwrite ($pfile, $txt);
	// Navigation header table
	$txt = "\t<div align=\"center\">\n\t<center>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t<table border=\"0\" width=\"100%\">\n\t\t\t<tr>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">";
	fwrite ($pfile, $txt);
	if ($pagenum === 1)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"expage" . (sprintf("%'.03d", $pagenum - 1)) . ".html\"><button class=\"button exbutton\">Previous Page</button></a></td>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">\n\t\t\t\t\t<a href=\"exframe" . $upframe . ".html\"><button class=\"button exbutton\">Back to Frame Page " . ($pagenum - (($pagenum - 1) % 16)) . "</button></a>\n\t\t\t\t</td>\n";
	fwrite ($pfile, $txt);
	if ($pagenum === $numpages)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "\t\t\t\t<td>\n\t\t\t\t\t<a href=\"expage" . (sprintf("%'.03d", $pagenum +	1)) . ".html\"><button class=\"button exbutton\">Next Page</button></a>\n\t\t\t\t</td>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t</tr>\n\t\t</table>\n\t</center>\n";
	fwrite ($pfile, $txt);
	// Exhibit Page table
	$txt = "\t<center>\n\t\t<table>\n\t\t\t<tr>\n\t\t\t\t<td>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t\t\t<map name=\"FPMap0\">\n";
	fwrite ($pfile, $txt);
	// print_r($pagenum . "\n");
	$exitems = getitems($pagenum);
	// ($exitems);
	// Need to fix the .jpg hard codes
	if (is_array($exitems) || is_object($exitems))
	{	foreach((array) $exitems as $exitem) {
			// print_r($exitem . "\n");
			$txt = "\t\t\t\t\t\t<area href=\"" . $exitem . "\" target=\"_blank\" shape=\"rect\" coords=\"50, 50, 100, 100\" />\n";
			fwrite ($pfile, $txt);
		}
	} else {
		$txt = "\t\t\t\t\t\t<area href=\"nothing\" target=\"_blank\" shape=\"rect\" coords=\"50, 50, 100, 100\" />\n";
	}
	$txt = "\t\t\t\t\t\t<area href=\"f" . (sprintf("%'.03d", $pagenum)) . ".jpg\" target=\"_blank\" shape=\"default\" />\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t\t\t</map>\n\t\t\t\t<img class=\"dropshadow\" border=\"0\" src=\"p" . (sprintf("%'.03d", $pagenum)) . ".jpg\" usemap=\"#FPMap0\" />\n";
	fwrite ($pfile, $txt);
	$txt="\t\t\t</td>\n\t\t</tr>\n\t</table>\n</center>\n";
	fwrite ($pfile, $txt);
}

// End clock time in seconds 
$end_time = microtime(true); 
  
// Calculate script execution time 
$execution_time = ($end_time - $start_time); 
  
echo " Execution time of script = ".$execution_time." sec"; 
?>
