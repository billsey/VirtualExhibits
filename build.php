<?php
	ini_set('memory_limit', '256M');
	ini_set('max_execution_time', '600'); //600 seconds = 10 minutes should be more than enough

	// Turn off output buffering
	ini_set('output_buffering', 'off');
	// Turn off PHP output compression
	ini_set('zlib.output_compression', false);
			 
	//Flush (send) the output buffer and turn off output buffering
	while (@ob_end_flush());
	   
	// Implicitly flush the buffer(s)
	ini_set('implicit_flush', true);
	ob_implicit_flush(true);
	ob_start();

// Starting clock time in seconds 
$start_time = microtime(true); 
$a=1; 

	$t = array(); // thumbnails
	$f = array(); // Full Sized
	$p = array(); // Page Sized
	$i = array(); // Individual Items
	$items = array(); // Subset, individual items from a single page
	
	// Create variables for use when parsing a file name.
	$atype = ""; // Array type; f, p, t, i
	$anum = 0; // Page number
	$enum = 0; // Item number on page
	$ftype = ""; // File type (.jpg, .png, etc.)

	$dir    = './'; // Current Directory
	$files = scandir($dir); // Scan for files in directory, alphabetically sorted

	$f = array_filter($files, "full"); // Calls full() on file list array, assumes full size files are correctly named

	$count = 0;
	// Step through list of full sized files and create page and thumb files
	foreach($f as $exfull) {
		getelement($exfull); // filename is parsed for page number and file type
		$count++;
		if ($count == $anum) {
			$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			statusOut("Create Page Image File: " . $expage . "\n");
			resizeImage($exfull, $expage, 0, 1100, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			statusOut("Create Thumbnail Image File " . $expage . "\n");
			resizeImage($exfull, $exthumb, 0, 275, 100); // Resize full page to thumbnail filename.
		} else {
			$exfull = "f" . sprintf("%'.03d", ($count - 1)) . $ftype; // Rebuild old full filename
			$expage = "p" . sprintf("%'.03d", ($count - 1)) . $ftype; // Build page filename
			// Double width pages
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			statusOut("Create Double Page Image File " . $expage . "\n");
			resizeImage($exfull, $expage, 0, 1100, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", ($count - 1)) . $ftype; // Build thumbnail filename.
			// Double width pages
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			statusOut("Create Double Thumbnail Image File " . $expage . "\n");
			resizeImage($exfull, $exthumb, 0, 275, 100); // Resize full page to thumbnail filename.
			$count++;
			$exfull = "f" . sprintf("%'.03d", ($count)) . $ftype; // Rebuild new full filename
			$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $expage, 0, 1100, 100); // Resize full page to page filename. 
			$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
			// Single width pages, maybe
			// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
			resizeImage($exfull, $exthumb, 0, 275, 100); // Resize full page to thumbnail filename.
		}
	}
	if ($count % 16 <> 0) // Not the last page in a frame
	{
		$count++;
		$expage = "p" . sprintf("%'.03d", $anum) . $ftype; // Build page filename
//		print_r("exfull: " . $exfull . " Count: " . $count . " expage: " . $expage . "\n");
		// Single width pages, maybe
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		statusOut("Create Double Page Image File " . $expage . ", second chance.\n");
		resizeImage($exfull, $expage, 0, 1100, 100); // Resize full page to page filename. 
		$exthumb = "t" . sprintf("%'.03d", $anum) . $ftype; // Build thumbnail filename.
//		print_r("exfull: " . $exfull . " Count: " . $count . " exthumb: " . $exthumb . "\n");
		// Single width pages, maybe
		// Future: resize recognizing odd sized pages, such as three wide in a frame or double tall.
		statusOut("Create Double Thumbnail Image File " . $expage . ", second chance.\n");
		resizeImage($exfull, $exthumb, 0, 275, 100); // Resize full page to thumbnail filename.
	}
	
	// As above, rescans including page and thumb image files.
	$dir    = './';
	$files = scandir($dir);

	// Calls appropriate function for each additional file type.
	// Assumes item files have been created manually
	$t = array_filter($files, "thumb");
	$p = array_filter($files, "pic");
	$i = array_filter($files, "item");
	
	// Figure out how big the exhibit is. 
	// Since we incremented $count for missing pages the pages/16 should be close.
	// Future: potentially pull in frame layout from a data file.
	$numframes = intdiv($count, 16);
	statusOut("Number of Frames: " . $numframes . "\n");
	$numpages = $count; //May not be valid when accounting for double pages.
	statusOut("Number of Pages: " . $numpages . "\n");
	
// Now, build the frame page, or pages, using the thumb array and the frames count.
for ($iteration = 1; $iteration <= $numframes; $iteration++) {
	$framefile = fopen("exframe" . sprintf("%'.02d", $iteration) . ".html", "w") or die("Unable to open frame file!");
	buildframe($framefile, $iteration, $numframes);
}

// And then build the pages the frame links point to.
foreach ($p as $currpage) {
	global $i; 
	global $atype;
	global $anum;
	global $enum;
	global $ftype;
	global $nextatype;
	global $nextanum;
	global $nextenum;
	global $nextftype;
	global $prevatype;
	global $prevanum;
	global $prevenum;
	global $prevftype;
	global $numpages;
	
	$index = array_search($currpage, $p);
	$previndex = $index - 1;
	$nextindex = $index + 1;
//	print_r("Previous: " . $p[$previndex] . " Current: " . $p[$index] . " Next: " . $p[$nextindex] . "\n");

	getelement($currpage);
	if (array_key_exists($previndex, $p)) {
		getprevelement($p[$previndex]);
	} else { 
		$prevanum = 0;
	}
	if (array_key_exists($nextindex, $p)) {
		getnextelement($p[$nextindex]);
	} else { 
		$nextanum = 0;
	}
//	print_r("Previous: " . $prevanum . " Current: " . $anum . " Next: " . $nextanum . "\n");
	
	$pagefile = fopen("expage" . sprintf("%'.03d", $anum) . ".html", "w") or die("Unable to open page file!");
	buildpage($pagefile, $prevanum, $anum, $nextanum, $numpages, $i);
}

function statusOut($outtext)
{	// https://www.codeproject.com/Tips/680085/Real-time-Updating-of-PHP-Output 
	// This should work, but doesn't seem to. :(
	ob_flush();
	flush();

	echo str_pad("",1024," ");
	echo "<br />";

	echo $outtext;

	ob_flush();
	flush();
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

function getnextelement($a)
{
	 // Globals are from the main body of the script, so they don't have to be passed individually.
	global $nextatype;
	global $nextanum;
	global $nextenum;
	global $nextftype;

	// Gets a single element from an array and parses it for info
	if ($a <> null) {
		$nextatype = $a[0];
		$nextanum = intval(substr($a, 1, 3));
		$nextenum = intval(substr($a, 5, 2));
		$nextdotpos = strlen($a) - 4;
		$nextftype = substr($a, $nextdotpos, 4);
	}
}

function getprevelement($a)
{
	 // Globals are from the main body of the script, so they don't have to be passed individually.
	global $prevatype;
	global $prevanum;
	global $prevenum;
	global $prevftype;

	// Gets a single element from an array and parses it for info
	if ($a <> null) {
		$prevatype = $a[0];
		$prevanum = intval(substr($a, 1, 3));
		$prevenum = intval(substr($a, 5, 2));
		$prevdotpos = strlen($a) - 4;
		$prevftype = substr($a, $prevdotpos, 4);
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
	
	global $nextatype;
	global $nextanum;
	global $nextenum;
	global $nextftype;
	
	$nextatype = ""; // Array type; f, p, t, i
	$nextanum = 0; // Page number
	$nextenum = 0; // Item number on page
	$nextftype = ""; // File type (.jpg, .png, etc.)
	statusOut("Create Frame File: " . $framenum . "\n");

	// Initial page setup
	$titlefile = fopen("extitle.txt", "r") or die("Unable to open title file!");
	$extitle = fgets($titlefile);
	$title = $extitle . ": Frame " . $framenum;
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
	$txt = "  <div align=\"center\">\n  <center>\n";
	fwrite ($ffile, $txt);
	$txt = "    <table border=\"0\" width=\"100%\">\n      <tr>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"33%\">";
	fwrite ($ffile, $txt);
	if ($framenum === 1)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum - 1)) . ".html\"><button class=\"button exbutton\">Previous Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"34%\">\n          <a href=\"../../exhibits.html\"><button class=\"button exbutton\">Back to Exhibits Index Page</button></a>\n        </td>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"33%\">";
	fwrite ($ffile, $txt);
	if ($framenum === $numframes)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum + 1)) . ".html\"><button class=\"button exbutton\">Next Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	$txt = "      </tr>\n";
	fwrite ($ffile, $txt);
	$txt = "    </table>\n";
	fwrite ($ffile, $txt);
	// End of navigation header table
	
	// Exhibit Title
	$txt = "    <table border=\"0\" width=\"100%\">\n";
	fwrite ($ffile, $txt);
	$txt = "      <tr>\n        <td align=\"center\">";
	fwrite ($ffile, $txt);
	$txt = "<font size=\"+2\">" . $title . "</font>\n";
	fwrite ($ffile, $txt);
	$txt = "        </td>\n      </tr>\n";
	fwrite ($ffile, $txt);
	$txt = "    </table>\n    </center>\n  </div>\n";
	fwrite ($ffile, $txt);
	// End of navigation header and Title table
	
	$txt = "  <div align=\"center\">\n  <center>\n";
	fwrite ($ffile, $txt);
//	$txt = "  <table border=\"0\" width=\"100%\">\n";
//	fwrite ($ffile, $txt);
	
	$step = 1; // Remainder of page position on a frame row, we might have to deal with three wide pages as well as doubled
	foreach($p as &$value) {
		getelement($value);
		if ($anum <= ($framenum - 1) * 16) { // We are not yet to the frame
			continue;
		}
		if ($anum > $framenum * 16) { // We are past the current frame
			break;
		}
		$nextvalue = next($p);
		getnextelement($nextvalue);
		if (($index + $step == $anum) && (intdiv($anum - 1, 16) === ($framenum - 1))) { // Page position matches page number and we're in the right frame
			if ($step % 4 == 1) { // First page in a frame row
				$txt = "<table>\n  <tr>\n";
				fwrite ($ffile, $txt);
			}
			if (($nextanum === $anum + 2)  || (($nextanum === $anum) && ($anum % 16 === 15))) {
				$txt = "    <td align=\"center\" colspan=\"2\">\n";
				fwrite ($ffile, $txt);
			} else {
				$txt = "    <td align=\"center\">\n";
				fwrite ($ffile, $txt);
			}
			$txt = "      <a href=\"expage" . (sprintf("%'.03d", $anum)) . ".html\">\n";
			fwrite ($ffile, $txt);
			$txt = "      <img border=\"2\" src=\"t" .  (sprintf("%'.03d", $anum)) . $ftype . "\" /></a>\n    </td>\n";
			fwrite ($ffile, $txt);
			if (($step % 4 == 0) || (($step % 4 == 3) && ($nextanum === $anum + 2)) || (($step % 4 == 3) && ($nextanum === $anum))) { // Last page in a frame row
				$txt = "  </tr>\n</table>\n";
				fwrite ($ffile, $txt);
			}
			$step++;
			if ($nextanum === $anum + 2)
				$step++;
		}
		else
			print_r("Page No: " . ($index + $step) . " Num: " . $anum . "\n");
	}
	$txt = "</center>\n</div>\n";
	fwrite ($ffile, $txt);
	
	// Navigation footer table
	$txt = "  <div align=\"center\">\n  <center>\n";
	fwrite ($ffile, $txt);
	$txt = "    <table border=\"0\" width=\"100%\">\n      <tr>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"33%\">";
	fwrite ($ffile, $txt);
	if ($framenum === 1)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum - 1)) . ".html\"><button class=\"button exbutton\">Previous Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"34%\">\n          <a href=\"../../exhibits.html\"><button class=\"button exbutton\">Back to Exhibits Index Page</button></a>\n        </td>\n";
	fwrite ($ffile, $txt);
	$txt = "        <td align=\"center\" width=\"33%\">";
	fwrite ($ffile, $txt);
	if ($framenum === $numframes)
		$txt = "&nbsp;</td>\n";
	else
		$txt = "<a href=\"exframe" . (sprintf("%'.02d", $framenum + 1)) . ".html\"><button class=\"button exbutton\">Next Frame</button></a></td>\n";
	fwrite ($ffile, $txt);
	$txt = "      </tr>\n    </table>\n    </center>\n  </div>\n";
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

function buildpage($pfile, $prevpage, $pagenum, $nextpage, $numpages, $item) {
	global $items;
	global $i;

	global $atype;
	global $anum;
	global $enum;
	global $ftype;

	statusOut("Create Page File: " . $pagenum . "\n");

	// Initial page setup
	$titlefile = fopen("extitle.txt", "r") or die("Unable to open title file!");
	$extitle = fgets($titlefile);
	$title = $extitle . " Page " . $pagenum;
	$upframe = sprintf("%'.02d", (intdiv($pagenum + 15, 16)));
//	print_r("pagenum: " . $pagenum . " upframe: " . $upframe . "\n");
	$txt = "<!DOCTYPE html>\n<html>\n\n<head>";
	fwrite ($pfile, $txt);
	$txt = "<Title>" . $title . "</title>\n";
	fwrite ($pfile, $txt);
	$txt = "<link rel=\"stylesheet\" href=\"../exhibit.css\" />";
	fwrite ($pfile, $txt);
	$txt = "</head>\n<body>\n";
	fwrite ($pfile, $txt);
	// Navigation header table
	$txt = "  <div align=\"center\">\n  <center>\n";
	fwrite ($pfile, $txt);
	$txt = "    <table border=\"0\" width=\"100%\">\n      <tr>\n";
	fwrite ($pfile, $txt);
	$txt = "        <td align=\"center\" width=\"33%\">";
	fwrite ($pfile, $txt);
	if (!$prevpage)
		$txt = "        <td>\n          &nbsp;\n        </td>\n";
	else
		$txt = "<a href=\"expage" . (sprintf("%'.03d", $prevpage)) . ".html\"><button class=\"button exbutton\">Previous Page</button></a></td>\n";
	fwrite ($pfile, $txt);
	$txt = "        <td align=\"center\" width=\"34%\">\n          <a href=\"exframe" . $upframe . ".html\"><button class=\"button exbutton\">Back to Frame " . $upframe . "</button></a>\n        </td>\n";
	fwrite ($pfile, $txt);
	if (!$nextpage)
		$txt = "        <td width=\"33%\">\n          &nbsp;\n        </td>\n";
	else
		$txt = "        <td width=\"33%\">\n          <a href=\"expage" . (sprintf("%'.03d", $nextpage)) . ".html\"><button class=\"button exbutton\">Next Page</button></a>\n        </td>\n";
	fwrite ($pfile, $txt);
	$txt = "      </tr>\n    </table>\n  </center>\n";
	fwrite ($pfile, $txt);
	// Exhibit Page table
	$txt = "  <center>\n    <table>\n      <tr>\n        <td>\n";
	fwrite ($pfile, $txt);
	$txt = "          <map name=\"FPMap0\">\n";
	fwrite ($pfile, $txt);
	// print_r($pagenum . "\n");
	$exitems = getitems($pagenum);
	// Need to fix the .jpg hard codes
	if (is_array($exitems) || is_object($exitems)) {
		foreach((array) $exitems as $exitem) {
			getelement($exitem);
//			print_r("Anum: " . $anum . " Enum: " . $enum . "\n");
			// print_r($exitem . "\n");
			$txt = "            <area href=\"" . $exitem . "\" target=\"_blank\" shape=\"rect\" coords=\"50, 50, 100, 100\" />\n";
			fwrite ($pfile, $txt);
		}
	} else {
		$txt = "            <area href=\"nothing\" target=\"_blank\" shape=\"rect\" coords=\"50, 50, 100, 100\" />\n";
	}
	$txt = "            <area href=\"f" . (sprintf("%'.03d", $pagenum)) . $ftype . "\" target=\"_blank\" shape=\"default\" />\n";
	fwrite ($pfile, $txt);
	$txt = "          </map>\n        <img class=\"dropshadow\" border=\"0\" src=\"p" . (sprintf("%'.03d", $pagenum)) .  $ftype . "\" usemap=\"#FPMap0\" />\n";
	fwrite ($pfile, $txt);
	$txt="      </td>\n    </tr>\n  </table>\n</center>\n";
	fwrite ($pfile, $txt);
}

	// Jan Zikan, Czech Repulic, https://gist.github.com/janzikan
	function resizeImage($sourceImage, $targetImage, $maxWidth, $maxHeight, $quality = 80)
	{
		// Obtain image from given source file.
		if (!$image = @imageCreateFromAny($sourceImage))
		{
			return false;
		}

		// Get dimensions of source image.
		list($origWidth, $origHeight) = getimagesize($sourceImage);

		if ($maxWidth == 0)
		{
			$maxWidth  = $origWidth;
		}

		if ($maxHeight == 0)
		{
			$maxHeight = $origHeight;
		}

		// Calculate ratio of desired maximum sizes and original sizes.
		$widthRatio = $maxWidth / $origWidth;
		$heightRatio = $maxHeight / $origHeight;

		// Ratio used for calculating new image dimensions.
		$ratio = min($widthRatio, $heightRatio);

		// Calculate new image dimensions.
		$newWidth  = (int)$origWidth  * $ratio;
		$newHeight = (int)$origHeight * $ratio;

		// Create final image with new dimensions.
		$newImage = imagecreatetruecolor($newWidth, $newHeight);
		imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
		imagejpeg($newImage, $targetImage, $quality);

		// Free up the memory.
		imagedestroy($image);
		imagedestroy($newImage);

		return true;
	}

function imageCreateFromAny($filepath) {
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
    $allowedTypes = array(
        1,  // [] gif
        2,  // [] jpg
        3,  // [] png
    );
    if (!in_array($type, $allowedTypes)) {
        return false;
    }
    switch ($type) {
        case 1 :
            $im = imageCreateFromGif($filepath);
        break;
        case 2 :
            $im = imageCreateFromJpeg($filepath);
        break;
        case 3 :
            $im = imageCreateFromPng($filepath);
        break;
    }   
    return $im; 
} 
// End clock time in seconds 
$end_time = microtime(true); 
  
// Calculate script execution time 
$execution_time = ($end_time - $start_time); 
  
statusOut(" Execution time of script = ".$execution_time." sec"); 
?>
