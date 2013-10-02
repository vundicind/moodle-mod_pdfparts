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
 * Strings for component 'pdfparts', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    mod_pdfparts
 * @copyright  2013 Radu Dumbrăveanu  {@link http://vundicind.blogspot.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'PDF Parts';
$string['modulenameplural'] = 'PDF Parts';
$string['modulename_help'] = 'The PDF Parts module enables a teacher to provide a set of freely selected pages from a PDF file as a course resource.';
$string['pluginadministration'] = 'pdfparts administration';
$string['pluginname'] = 'PDF Parts';

$string['pdfpartsname_help'] = '';
$string['pdfparts'] = 'PDF Parts';

// settings
$string['framesize'] = ' Frame height';
$string['configframesize'] = 'When a web page or an uploaded file is displayed within a frame, this value is the height (in pixels) of the top frame (which contains the navigation).';
$string['displayoptions'] = 'Available display options';
$string['configdisplayoptions'] = 'Select all options that should be available, existing settings are not modified. Hold CTRL key to select multiple fields.';
$string['printheadingexplain'] = 'Display PDF name above content? Some display types may not display resource name even if enabled.';
$string['printintroexplain'] = 'Display PDF description below content? Some display types may not display description even if enabled.';
$string['displayselectexplain'] = 'Choose display type, unfortunately not all types are suitable for all files.';
$string['popupwidthexplain'] = 'Specifies default width of popup windows.';
$string['popupheightexplain'] = 'Specifies default height of popup windows.';

// edit
$string['name'] = 'Name';
$string['content'] = 'Content';
$string['selectfile'] = 'Select PDF file';
$string['appearance'] = 'Appearance';
$string['displayselect'] = 'Display';
$string['displayselect_help'] = 'This setting determines how the PDF file is displayed. Options may include:

* Automatic - The best display option for the PDF is selected automatically
* Embed - The PDF is displayed within the page below the navigation bar together with the PDF description and any blocks
* Open - Only the PDF is displayed in the browser window
* In pop-up - The PDF is displayed in a new browser window without menus or an address bar
* In frame - The PDF is displayed within a frame below the the navigation bar and PDF description
* New window - The PDF is displayed in a new browser window with menus and an address bar';
$string['pages'] = 'Pages';
$string['pages_help'] = 'ex. 1-3,5,8,10-12';
$string['popupwidth'] = 'Pop-up width (in pixels)';
$string['popupheight'] = 'Pop-up height (in pixels)';
$string['printheading'] = 'Display resource name';
$string['printintro'] = 'Display resource description';
