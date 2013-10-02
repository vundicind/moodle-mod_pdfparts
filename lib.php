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
 * Mandatory public API of pdfparts module
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function pdfparts_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_INTRO:         return true;
        default:                        return null;
    }
}

/**
 * Add pdfparts instance.
 *
 * @param object $data
 * @param object $mform
 * @return int new pdfparts instance id
 */
function pdfparts_add_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $data->timecreated = time();
    $data->timemodified = time();
    $cmid = $data->coursemodule;
    $data->id = $DB->insert_record('pdfparts', $data);
	
    // we need to use context now, so we need to make sure all needed info is already in db
    $DB->set_field('course_modules', 'instance', $data->id, array('id'=>$cmid));
    pdfparts_set_mainfile($data);
    return $data->id;
}

/**
 * Update pdfparts instance.
 *
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function pdfparts_update_instance($data, $mform) {
    global $CFG, $DB;
	require_once("$CFG->libdir/resourcelib.php");

    $data->timemodified = time();
    $data->id = $data->instance;
	$data->revision++;

    $DB->update_record('pdfparts', $data);
	pdfparts_set_mainfile($data);
    return true;
}

/**
 * Delete pdfparts instance.

 * @param int $id
 * @return bool true
 */
function pdfparts_delete_instance($id) {
    global $DB;

    if (!$data = $DB->get_record('pdfparts', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('pdfparts', array('id' => $data->id));
    return true;
}

/**
 * Return use outline

 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $resource
 * @return object|null
 */
function pdfparts_user_outline($course, $user, $mod, $pdfparts) {
    global $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'pdfparts',
                                              'action'=>'view', 'info'=>$pdfparts->id), 'time ASC')) {

        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new stdClass();
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;

        return $result;
    }
    return null;
}

/**
 * Return use complete

 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $resource
 */
function pdfparts_user_complete($course, $user, $mod, $resource) {
    global $CFG, $DB;

    if ($logs = $DB->get_records('log', array('userid'=>$user->id, 'module'=>'pdfparts',
                                              'action'=>'view', 'info'=>$resource->id), 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strmostrecently = get_string('mostrecently');
        $strnumviews = get_string('numviews', '', $numviews);

        echo "$strnumviews - $strmostrecently ".userdate($lastlog->time);

    } else {
        print_string('neverseen', 'pdfparts');
    }
}



/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return object info
 */
function pdfparts_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;
    require_once("$CFG->libdir/filelib.php");
    require_once("$CFG->dirroot/mod/pdfparts/locallib.php");
    require_once($CFG->libdir.'/completionlib.php');

    $context = context_module::instance($coursemodule->id);

    if (!$data = $DB->get_record('pdfparts', array('id'=>$coursemodule->instance), 'id, name, revision')) {
        return null;
    }

    $info = new cached_cm_info();
    $info->name = $data->name;

    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_pdfparts', 'content', 0, 'sortorder DESC, id ASC', false); // TODO: this is not very efficient!!
    if (count($files) >= 1) {
        $mainfile = reset($files);
        $info->icon = file_file_icon($mainfile, 24);
        $data->mainfile = $mainfile->get_filename();
    }

    $display = pdfparts_get_final_display_type($data);

    if ($display == RESOURCELIB_DISPLAY_POPUP) {
        $fullurl = "$CFG->wwwroot/mod/pdfparts/view.php?id=$coursemodule->id&amp;redirect=1";
        $options = empty($resource->displayoptions) ? array() : unserialize($resource->displayoptions);
        $width  = empty($options['popupwidth'])  ? 620 : $options['popupwidth'];
        $height = empty($options['popupheight']) ? 450 : $options['popupheight'];
        $wh = "width=$width,height=$height,toolbar=no,location=no,menubar=no,copyhistory=no,status=no,directories=no,scrollbars=yes,resizable=yes";
        $info->extra = "onclick=\"window.open('$fullurl', '', '$wh'); return false;\"";

    } else if ($display == RESOURCELIB_DISPLAY_NEW) {
        $fullurl = "$CFG->wwwroot/mod/pdfparts/view.php?id=$coursemodule->id&amp;redirect=1";
        $info->extra = "onclick=\"window.open('$fullurl'); return false;\"";

    }

    return $info;
}



/**
 * Lists all browsable file areas

 * @param object $course
 * @param object $cm
 * @param object $context
 * @return array
 */
function pdfparts_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['content'] = get_string('resourcecontent', 'resource');
    return $areas;
}

/**
 * File browsing support for resource module content area.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $browser file browser instance
 * @param stdClass $areas file areas
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param int $itemid item ID
 * @param string $filepath file path
 * @param string $filename file name
 * @return file_info instance or null if not found
 */
function pdfparts_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG;

    if (!has_capability('moodle/course:managefiles', $context)) {
        // students can not peak here!
        return null;
    }

    $fs = get_file_storage();

    if ($filearea === 'content') {
        $filepath = is_null($filepath) ? '/' : $filepath;
        $filename = is_null($filename) ? '.' : $filename;

        $urlbase = $CFG->wwwroot.'/pluginfile.php';
        if (!$storedfile = $fs->get_file($context->id, 'mod_pdfparts', 'content', 0, $filepath, $filename)) {
            if ($filepath === '/' and $filename === '.') {
                $storedfile = new virtual_root_file($context->id, 'mod_pdfparts', 'content', 0);
            } else {
                // not found
                return null;
            }
        }
        require_once("$CFG->dirroot/mod/pdfparts/locallib.php");
        return new pdfparts_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, true, false);
    }

    // note: resource_intro handled in file_browser automatically

    return null;
}


/**
 * Serves the resource files.
 *
 * @package  mod_resource
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function pdfparts_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);
    if (!has_capability('mod/resource:view', $context)) {
        return false;
    }

    if ($filearea !== 'content') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    array_shift($args); // ignore revision - designed to prevent caching problems only

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = rtrim("/$context->id/mod_pdfparts/$filearea/0/$relativepath", '/');
    do {
        if (!$file = $fs->get_file_by_hash(sha1($fullpath))) {
            if ($fs->get_file_by_hash(sha1("$fullpath/."))) {
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.htm"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/index.html"))) {
                    break;
                }
                if ($file = $fs->get_file_by_hash(sha1("$fullpath/Default.htm"))) {
                    break;
                }
            }
            $resource = $DB->get_record('resource', array('id'=>$cm->instance), 'id, legacyfiles', MUST_EXIST);
            if ($resource->legacyfiles != RESOURCELIB_LEGACYFILES_ACTIVE) {
                return false;
            }
            if (!$file = resourcelib_try_file_migration('/'.$relativepath, $cm->id, $cm->course, 'mod_pdfparts', 'content', 0)) {
                return false;
            }
            // file migrate - update flag
            $resource->legacyfileslast = time();
            $DB->update_record('resource', $resource);
        }
    } while (false);

    // should we apply filters?
    $mimetype = $file->get_mimetype();
    if ($mimetype === 'text/html' or $mimetype === 'text/plain') {
        $filter = $DB->get_field('resource', 'filterfiles', array('id'=>$cm->instance));
        $CFG->embeddedsoforcelinktarget = true;
    } else {
        $filter = 0;
    }

    // finally send the file
    send_stored_file($file, 86400, $filter, $forcedownload);
}

/**
 * Return a list of page types

 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function pdfparts_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-resource-*'=>get_string('page-mod-resource-x', 'resource'));
    return $module_pagetype;
}


/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in pdfparts activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 */
function pdfparts_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  //  True if anything was printed, otherwise false
}

/**
 * Returns all other caps used in module
 * @return array
 */
function pdfparts_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}
