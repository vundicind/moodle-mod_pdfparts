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
 * Pdfparts module admin settings and defaults
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_AUTO,
                                                           RESOURCELIB_DISPLAY_DOWNLOAD,
                                                           RESOURCELIB_DISPLAY_OPEN,
                                                           RESOURCELIB_DISPLAY_EMBED,
                                                           RESOURCELIB_DISPLAY_FRAME,
                                                           RESOURCELIB_DISPLAY_NEW,
                                                           RESOURCELIB_DISPLAY_POPUP,
                                                          ));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_AUTO,
                                   RESOURCELIB_DISPLAY_DOWNLOAD,
                                   RESOURCELIB_DISPLAY_OPEN,
                                  );

    //--- general settings -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_configtext('pdfparts/framesize',
        get_string('framesize', 'pdfparts'), get_string('configframesize', 'pdfparts'), 130, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('pdfparts/requiremodintro',
        get_string('requiremodintro', 'admin'), get_string('configrequiremodintro', 'admin'), 1));
    $settings->add(new admin_setting_configmultiselect('pdfparts/displayoptions',
        get_string('displayoptions', 'pdfparts'), get_string('configdisplayoptions', 'pdfparts'),
        $defaultdisplayoptions, $displayoptions));

    //--- modedit defaults -----------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('pdfpartsmodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('pdfparts/printheading',
        get_string('printheading', 'pdfparts'), get_string('printheadingexplain', 'pdfparts'), 0));
    $settings->add(new admin_setting_configcheckbox('resource/printintro',
        get_string('printintro', 'pdfparts'), get_string('printintroexplain', 'pdfparts'), 1));
    $settings->add(new admin_setting_configselect('pdfparts/display',
        get_string('displayselect', 'pdfparts'), get_string('displayselectexplain', 'pdfparts'), RESOURCELIB_DISPLAY_DOWNLOAD, $displayoptions));
    $settings->add(new admin_setting_configtext('pdfparts/popupwidth',
        get_string('popupwidth', 'resource'), get_string('popupwidthexplain', 'pdfparts'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('pdfparts/popupheight',
        get_string('popupheight', 'pdfparts'), get_string('popupheightexplain', 'pdfparts'), 450, PARAM_INT, 7));
}
