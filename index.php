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
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

$id = required_param('id', PARAM_INT);   // course id

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course, true);

add_to_log($course->id, 'pdfparts', 'view all', "index.php?id=$course->id", '');

$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

$PAGE->set_url('/mod/pdfparts/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strpdfpartss);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpdfpartss);
$PAGE->set_context($coursecontext);

echo $OUTPUT->header();

if (!$pdfpartss = get_all_instances_in_course('pdfparts', $course)) {
    notice(get_string('thereareno', 'moodle', $strpdfpartss), "$CFG->wwwroot/course/view.php?id=$course->id");
}

if ($course->format == 'weeks') {
    $table->head = array(get_string('week'), get_string('name'));
    $table->align = array('center', 'left');
} else if ($course->format == 'topics') {
    $table->head = array(get_string('topic'), get_string('name'));
    $table->align = array('center', 'left', 'left', 'left');
} else {
    $table->head = array(get_string('name'));
    $table->align = array('left', 'left', 'left');
}

foreach ($pdfpartss as $pdfparts) {
    if (!$pdfparts->visible) {
        $link = html_writer::link(
            new moodle_url('/mod/pdfparts.php', array('id' => $pdfparts->coursemodule)),
            format_string($pdfparts->name, true),
            array('class' => 'dimmed'));
    } else {
        $link = html_writer::link(
            new moodle_url('/mod/pdfparts.php', array('id' => $pdfparts->coursemodule)),
            format_string($pdfparts->name, true));
    }

    if ($course->format == 'weeks' or $course->format == 'topics') {
        $table->data[] = array($pdfparts->section, $link);
    } else {
        $table->data[] = array($link);
    }
}

echo $OUTPUT->heading(get_string('modulenameplural', 'pdfparts'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();
