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
 * List of all pdfparts in course
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require("../../config.php");

$id = required_param('id', PARAM_INT);   // course id

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

add_to_log($course->id, 'pdfparts', 'view all', "index.php?id=$course->id", '');

$strpdfparts       = get_string('modulename', 'pdfparts');
$strpdfpartss      = get_string('modulenameplural', 'pdfparts');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/pdfparts/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strpdfpartss);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpdfpartss);
echo $OUTPUT->header();

if (!$pdfpartss = get_all_instances_in_course('pdfparts', $course)) {
    notice(get_string('thereareno', 'moodle', $strpdfpartss), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($pdfpartss as $pdfparts) {
    $cm = $modinfo->cms[$pdfparts->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($pdfparts->section !== $currentsection) {
            if ($pdfparts->section) {
                $printsection = get_section_name($course, $pdfparts->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $url->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($pdfparts->timemodified)."</span>";
    }

//    $extra = empty($cm->extra) ? '' : $cm->extra;
//    $icon = '';
//    if (!empty($cm->icon)) {
//        // each pdfparts has an icon in 2.0
//        $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
//    }    

    $class = $pdfparts->visible ? 'class="123"' : 'class="dimmed"'; // hidden modules are dimmed
//    $table->data[] = array (
//        $printsection,
//        "<a $class $extra href=\"view.php?id=$cm->id\">".format_string($pdfparts->name)."</a>",
//        format_module_intro('pdfparts', $pdfparts, $cm->id));
    $table->data[] = array (
        $printsection,
        html_writer::link(new moodle_url('view.php', array('id' => $cm->id)), format_string($pdfparts->name), $class),
        format_module_intro('pdfparts', $pdfparts, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
