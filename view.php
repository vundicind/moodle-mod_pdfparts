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
 * Pdfparts module version information
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../config.php');
require_once("$CFG->dirroot/mod/pdfparts/locallib.php");
require_once("$CFG->dirroot/mod/url/lib.php");
require_once("$CFG->libdir/completionlib.php");

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$p  = optional_param('p', 0, PARAM_INT);  // pdfparts instance ID - it should be named as the first character of the module
$pages  = optional_param('pages', 0, PARAM_TEXT);  // page range - overrides the value form DB
$redirect = optional_param('redirect', 0, PARAM_BOOL);

if ($id) {
  $cm         = get_coursemodule_from_id('pdfparts', $id, 0, false, MUST_EXIST);
  $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
  $pdfparts  = $DB->get_record('pdfparts', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($p) {
	$pdfparts  = $DB->get_record('pdfparts', array('id' => $p), '*', MUST_EXIST);
	$course     = $DB->get_record('course', array('id' => $pdfparts->course), '*', MUST_EXIST);
	$cm         = get_coursemodule_from_instance('pdfparts', $pdfparts->id, $course->id, false, MUST_EXIST);
} else {
	error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/resource:view', $context);

add_to_log($course->id, 'pdfparts', 'view', "view.php?id={$cm->id}", $pdfparts->name, $cm->id);

// Update 'viewed' state if required by completion system
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/pdfparts/view.php', array('id' => $cm->id));

$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_pdfparts', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
if (count($files) < 1) {
    resource_print_filenotfound($pdfparts, $cm, $course);
    die;
} else {
    $file = reset($files);
    unset($files);
}
$pdfparts->mainfile = $file->get_filename();

$displaytype = pdfparts_get_final_display_type($pdfparts);

// override the property loaded from DB with the values from the GET request 
if($pages) {
    $pdfparts->pages = $pages;// I don't like this ideea, but...
}
/*
if ($displaytype == RESOURCELIB_DISPLAY_OPEN || $displaytype == RESOURCELIB_DISPLAY_DOWNLOAD) {
    // For 'open' and 'download' links, we always redirect to the content - except
    // if the user just chose 'save and display' from the form then that would be
    // confusing
    if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'modedit.php') === false) {
        $redirect = true;
    }
}

if ($redirect) {
    // coming from course page or url index page
    // this redirect trick solves caching problems when tracking views ;-)
    $path = '/'.$context->id.'/mod_pdfparts/content/'.$pdfparts->revision.$file->get_filepath().$file->get_filename();
    $fullurl = moodle_url::make_file_url('/pluginfile.php', $path, $displaytype == RESOURCELIB_DISPLAY_DOWNLOAD);
    redirect($fullurl);
}*/

switch ($displaytype) {
    case RESOURCELIB_DISPLAY_EMBED:
        pdfparts_display_embed($pdfparts, $cm, $course, $file);
        break;
    case RESOURCELIB_DISPLAY_FRAME:
        pdfparts_display_frame($pdfparts, $cm, $course, $file);
        break;
    case RESOURCELIB_DISPLAY_OPEN:
        pdfparts_display_open($pdfparts, $cm, $course, $file);  
        break;
    case RESOURCELIB_DISPLAY_DOWNLOAD:
        pdfparts_display_download($pdfparts, $cm, $course, $file);  
        break;
    case RESOURCELIB_DISPLAY_NEW:
        pdfparts_display_new($pdfparts, $cm, $course, $file);  
        break;
    case RESOURCELIB_DISPLAY_POPUP:
        pdfparts_display_popup($pdfparts, $cm, $course, $file);  
        break;
    default:
        pdfparts_print_workaround($pdfparts, $cm, $course, $file);
        break;
}
