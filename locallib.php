<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Private pdfparts module utility functions
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");
require_once("$CFG->dirroot/mod/pdfparts/lib.php");

/**
 * Decide the best diaply format.

 * @param object $pdfparts
 * @return int display type constant
 */
function pdfparts_get_final_display_type($pdfparts) {
    global $CFG, $PAGE;

    if ($pdfparts->display != RESOURCELIB_DISPLAY_AUTO) {
        return $pdfparts->display;
    }
    
    if ($pdfparts->display == RESOURCELIB_DISPLAY_AUTO) {
        // In perfect world here we should test what current browser can support.
        // For example, if it can not open a PDF file then change the display type to DOWNLOAD etc.
        return RESOURCELIB_DISPLAY_OPEN;
    }

    if (empty($pdfparts->mainfile)) {
        return RESOURCELIB_DISPLAY_DOWNLOAD;
    } 

    // let the browser deal with it somehow
    return RESOURCELIB_DISPLAY_OPEN;
}

/**
 * Saves the file from pdfparts record somewhere on the server.

 * @param object $data
 */
function pdfparts_set_mainfile($data) {
	global $DB;

    $fs = get_file_storage();
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;

    $context = context_module::instance($cmid);
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_pdfparts', 'content', 0, array('subdirs'=>true));
    }
    $files = $fs->get_area_files($context->id, 'mod_pdfparts', 'content', 0, 'sortorder', false);
    if (count($files) == 1) {
        // only one file attached, set it as main file automatically
        $file = reset($files);
        file_set_sortorder($context->id, 'mod_pdfparts', 'content', 0, $file->get_filepath(), $file->get_filename(), 1);
    }
}


/**
 * Parse a page range. This is similar to the Print page Office that allows you to select the page or page range to print. 

 * @param object $pdfparts
 * @param int $pdfpagecount
 * @return array array of ranges
 */
function pdfparts_parse_page_range($pdfparts, $pdfpagecount) {
    header("Content-type: text/html"); 
    echo($pdfparts->pages);
    
	$anarray = explode(",", $pdfparts->pages);
	foreach ($anarray as &$value) {
		if(!strstr($value, "-")) {
			$value = intval($value);
		}
		else {
			$anotherarray = explode("-", $value);
			if(count($anotherarray) > 1) {
				$anotherarray[0] = intval(empty($anotherarray[0])?1:$anotherarray[0]);
				$anotherarray[1] = intval(empty($anotherarray[1])?$pdfpagecount:$anotherarray[1]);
				$value = $anotherarray;
			}
		}
	}
	return $anarray;
}

/**
 * Extract selected pages from PDF files using Zend_Pdf library.

 * @param object $pdfparts
 * @param object $file
 * @return string extracted pages in one PDF file (suitable for direct output to browser or saveing to disk)
 */
function pdfparse_pdf_zend_extract($pdfparts, $file) {
    global $CFG;
	try {    
        require_once("$CFG->dirroot/mod/pdfparts/Zend/Pdf.php");

        // load PDF document from file contents
        $pdf = Zend_Pdf::parse(trim($file->get_content()));
        $pdfpagecount = count($pdf->pages);
        
        // parse page range
        $pagerange = pdfparts_parse_page_range($pdfparts, $pdfpagecount);
        
        // new PDF for output		
        $outpdf = new Zend_Pdf();
        // new PDF page extractor
        $extractor = new Zend_Pdf_Resource_Extractor();

        // good example: http://framework.zend.com/manual/1.12/en/zend.pdf.pages.html#zend.pdf.pages.cloning
		foreach($pagerange as $value) {
			if(is_int($value)) {
				$outpdf->pages[] = $extractor->clonePage($pdf->pages[$value-1]);
			}
			elseif(is_array($value)) {
				for($k=$value[0]; $k<=$value[1]; $k++) {
					$outpdf->pages[] = $extractor->clonePage($pdf->pages[$k-1]);
				}
			}
		}
        
		// get PDF document as a string 
		$pdfdata = $outpdf->render(); 
		
		return $pdfdata;
	} catch (Zend_Pdf_Exception $e) {
	    print_error("Could not process the PDF file aosciated with this instance of the PDFParts module.");
	} catch (Exception $e) { 
	    print_error("Unknown error while processing the PDF file aosciated with this instance of the PDFParts module.");
	    return null;
	}
}

/**
 * Display embedded PDF file.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_embed($pdfparts, $cm, $course, $file) {
    print_error("This display type (embed) is not yet supported.");
}

/**
 * Display frame PDF file.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_frame($pdfparts, $cm, $course, $file) {
    print_error("This display type (frame) is not yet supported");
}

/**
 * Display PDF file.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_open($pdfparts, $cm, $course, $file) {
    $pdfdata = pdfparse_pdf_zend_extract($pdfparts, $file);
    if($pdfdata != null) {
        header("Content-type: application/x-pdf"); 
        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $pdfparts->timemodified).' GMT', true, 200);
        //header("Content-Length:");
        echo $pdfdata;
    }
}

/**
 * Force browser to download the PDF file.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_download($pdfparts, $cm, $course, $file) {
    $pdfdata = pdfparse_pdf_zend_extract($pdfparts, $file);
    if($pdfdata != null) {
        header("Content-type: application/x-pdf");    
        //header("Content-Disposition: inline; filename=\"$file->get_filename()_$pdfparts->pages\"");//-RD01102013
        header("Content-Disposition: inline; filename=\"$pdfparts->name (p$pdfparts->pages)\"");
        //header("Content-Length:");
        echo $pdfdata;
    }
}

/**
 * Display PDF file in a new windows.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_new($pdfparts, $cm, $course, $file) {
    print_error("This display type (new) is not yet supported");
}

/**
 * Display popup PDF file.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_display_popup($pdfparts, $cm, $course, $file) {
    print_error("This display type (popup) is not yet supported");
}
 
/**
 * Plan B.
 *
 * @param object $resource
 * @param object $cm
 * @param object $course
 * @param stored_file $file main file
 * @return does not return
 */
function pdfparts_print_workaround($pdfparts, $cm, $course, $file) {
    print_error("Could not determine the display type");
}
