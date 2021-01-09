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
	$count = 0;
	// Step through list of full sized files and create page and thumb files
	foreach($f as $exfull) {
		getelement($exfull); // filename is parsed for page number and file type
		$count++;
		if ($count == $anum) {
/*
			$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
//			print_r("exfull: " . $exfull . " Count: " . $count . " expage: " . $expage . "\n");
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $expage, 800, 0, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
//			print_r("exfull: " . $exfull . " Count: " . $count . " exthumb: " . $exthumb . "\n");
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $exthumb, 200, 0, 100); // Resize full page to thumbnail filename. */
		} else {
/*			$exfull = "f" . sprintf("%'.03d", ($count - 1)) . $ftype; // Rebuild old full filename
			$expage = "p" . sprintf("%'.03d", ($count - 1)) . $ftype; // Build page filename
//			print_r("exfull: " . $exfull . " Count: " . $count . " expage: " . $expage . "\n");
			// Double width pages
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $expage, 1600, 0, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", ($count - 1)) . $ftype; // Build thumbnail filename.
//			print_r("exfull: " . $exfull . " Count: " . $count . " exthumb: " . $exthumb . "\n");
			// Double width pages
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $exthumb, 400, 0, 100); // Resize full page to thumbnail filename. */
			$count++;
/*			$exfull = "f" . sprintf("%'.03d", ($count)) . $ftype; // Rebuild new full filename
			$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
//			print_r("exfull: " . $exfull . " Count: " . $count . " expage: " . $expage . "\n");
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $expage, 800, 0, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
//			print_r("exfull: " . $exfull . " Count: " . $count . " exthumb: " . $exthumb . "\n");
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $exthumb, 200, 0, 100); // Resize full page to thumbnail filename. */
		}
	}
	if ($count % 16 <> 0) // Not the last page in a frame
	{
		$count++;
/*		$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
//		print_r("exfull: " . $exfull . " Count: " . $count . " expage: " . $expage . "\n");
		// Single width pages, maybe
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		resizeImage($exfull, $expage, 1600, 0, 100); // Resize full page to page filename. 
		$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
//		print_r("exfull: " . $exfull . " Count: " . $count . " exthumb: " . $exthumb . "\n");
		// Single width pages, maybe
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		resizeImage($exfull, $exthumb, 400, 0, 100); // Resize full page to thumbnail filename.
	*/
	}
	// As above, rescans including page and thumb image files.
	$dir    = './';
	$files = scandir($dir);

	// Calls appropriate function for each additional file type.
	// Assumes item files have been created manually
	$t = array_filter($files, "thumb");
	$p = array_filter($files, "pic");
	$i = array_filter($files, "item");
	/*
	$keys = array_keys($p); // For use in determining what page is next
	for ($inc = 0; $inc < count($keys); $inc++) {
		$cur = $p[$keys[$inc]];
		$next = $p[$keys[$inc+1]];
	}
	*/
	// Figure out how big the exhibit is. 
	// Since we incremented $count for missing pages the pages/16 should be close.
	// Future: potentially pull in frame layout from a data file.
	$numframes = intdiv($count, 16);
	print_r("Number of Frames: " . $numframes . "\n");
	$numpages = $count; //May not be valid when accounting for double pages.
	print_r("Number of Pages: " . $numpages . "\n");
	
	// Creat variables for use when parsing a file name.
	$atype = ""; // Array type; f, p, t, i
	$anum = 0; // Page number
	$enum = 0; // Item number on page
	$ftype = ""; // File type (.jpg, .png, etc.)


// Now, build the frame page, or pages, using the thumb array and the frames count.
for ($iteration = 1; $iteration <= $numframes; $iteration++) {
	$framefile = fopen("exframe" . sprintf("%'.02d", $iteration) . ".html", "w") or die("Unable to open frame file!");
	buildframe($framefile, $iteration, $numframes);
}

// And then build the pages the frame links point to.
for ($iteration = 1; $iteration <= $count; $iteration++) {
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
	$index = ($framenum - 1) * 16; // Posiiton within a frame
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
	$txt = "\t\t<table border=\"0\" width=\"100\%\">\n\t\t\t<tr>\n";
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
	
	$step = 1; // Remainder of page position on a frame row, we might have to deal with three wide pages as well as doubled
	foreach($p as &$value) {
		getelement($value);
		print_r("Index: " . $index . " Step: " . $step . " Anum: " . $anum . " Framenum: " . $framenum . "\n");
		if ($anum < ($framenum - 1) * 16) { // We are not yet to the frame
			print_r ("Before Frame.\n");
			continue;
		}
		if ($anum > $framenum * 16) { // We are past the current frame
			print_r ("After Frame.\n");
			break;
		}
		else if (($index + $step == $anum) && (intdiv($anum - 1, 16) === ($framenum - 1))) { // Page position matches page number and we're in the right frame
			if ($step % 4 == 1) { // First page in a frame row
				$txt = "\t<tr>\n";
				fwrite ($ffile, $txt);
			}
			print_r("Normal Page\n");
			$txt = "\t\t<td align=\"center\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t<a href=\"expage" . (sprintf("%'.03d", $anum)) . ".html\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t<img border=\"2\" src=\"t" .  (sprintf("%'.03d", $anum)) . $ftype . "\" /></a>\n\t\t</td>\n";
			fwrite ($ffile, $txt);
			if ($step % 4 == 0) { // Last page in a frame row
				$txt = "\t</tr>\n";
				fwrite ($ffile, $txt);
			}
			$step++;
		}
		else if (($index + $step < $anum) && (intdiv($anum - 1, 16) === ($framenum - 1))) { // Page position doesn't match page number, so we missed a page
			if ($step % 4 == 1) { // First page in a frame row
				$txt = "\t<tr>\n";
				fwrite ($ffile, $txt);
			}
			print_r("Missing page\n");
			$txt = "\t\t<td align=\"center\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t \n\t\t</td>\n";
			fwrite ($ffile, $txt);
			if ($step % 4 == 0) { // Last page in a frame row
				$txt = "\t</tr>\n";
				fwrite ($ffile, $txt);
			}
			$step++;
			if ($step % 4 == 1) { // First page in a frame row
				$txt = "\t<tr>\n";
				fwrite ($ffile, $txt);
			}
			$txt = "\t\t<td align=\"center\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t<a href=\"expage" . (sprintf("%'.03d", $anum)) . ".html\">\n";
			fwrite ($ffile, $txt);
			$txt = "\t\t\t<img border=\"2\" src=\"t" .  (sprintf("%'.03d", $anum)) . $ftype . "\" /></a>\n\t\t</td>\n";
			fwrite ($ffile, $txt);
			if ($step % 4 == 0) { // Last page in a frame row
				$txt = "\t</tr>\n";
				fwrite ($ffile, $txt);
			}
			$step++;
		}
		else
		print_r("Page No: " . ($index + $step) . " Num: " . $anum . "\n");
	}
	$txt = "\t</tbody>\n</table>\n</center>\n</div>\n";
	fwrite ($ffile, $txt);
	
	// Navigation footer table
	$txt = "\t<div align=\"center\">\n\t<center>\n";
	fwrite ($ffile, $txt);
	$txt = "\t\t<table border=\"0\" width=\"100\%\">\n\t\t\t<tr>\n";
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
	$txt = "\t\t</table>\n\t\t</center>\n\t</div>\n";
	fwrite ($ffile, $txt);
	// Author blurb, update date/time
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

	global $atype;
	global $anum;
	global $enum;
	global $ftype;

	// Initial page setup
	$titlefile = fopen("extitle.txt", "r") or die("Unable to open title file!");
	$extitle = fgets($titlefile);
	$title = $extitle . " Page " . $pagenum;
	$upframe = sprintf("%'.02d", (intdiv($pagenum, 16) + 1));
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
		$txt = "\t\t\t\t<td>\n\t\t\t\t\t&nbsp;\n\t\t\t\t</td>\n";
	else
		$txt = "<a href=\"expage" . (sprintf("%'.03d", $pagenum - 1)) . ".html\"><button class=\"button exbutton\">Previous Page</button></a></td>\n";
	fwrite ($pfile, $txt);
	$txt = "\t\t\t\t<td align=\"center\">\n\t\t\t\t\t<a href=\"exframe" . $upframe . ".html\"><button class=\"button exbutton\">Back to Frame Page " . $upframe . "</button></a>\n\t\t\t\t</td>\n";
	fwrite ($pfile, $txt);
	if ($pagenum === $numpages)
		$txt = "\t\t\t\t<td>\n\t\t\t\t\t&nbsp;\n\t\t\t\t</td>\n";
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
	// Need to fix the .jpg hard codes
	if (is_array($exitems) || is_object($exitems)) {
		foreach((array) $exitems as $exitem) {
			getelement($exitem);
			print_r("Anum: " . $anum . " Enum: " . $enum . "\n");
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
