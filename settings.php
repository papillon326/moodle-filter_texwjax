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
 * TeX filter settings
 *
 * @package    filter
 * @subpackage tex
 * @copyright  2007 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    require_once($CFG->dirroot.'/filter/texwjax/lib.php');

    $items = array();
    $items[] = new admin_setting_heading('filter_texwjax/latexheading', get_string('latexsettings', 'filter_texwjax'), '');
    $items[] = new admin_setting_configtextarea('filter_texwjax/latexpreamble', get_string('latexpreamble','filter_texwjax'),
                   '', "\\usepackage[latin1]{inputenc}\n\\usepackage{amsmath}\n\\usepackage{amsfonts}\n\\RequirePackage{amsmath,amssymb,latexsym}\n");
    $items[] = new admin_setting_configtext('filter_texwjax/latexbackground', get_string('backgroundcolour', 'admin'), '', '#FFFFFF');
    $items[] = new admin_setting_configtext('filter_texwjax/density', get_string('density', 'admin'), '', '120', PARAM_INT);

    $default_filter_texwjax_pathlatex   = '';
    $default_filter_texwjax_pathdvips   = '';
    $default_filter_texwjax_pathdvisvgm = '';
    $default_filter_texwjax_pathconvert = '';
    if (PHP_OS=='Linux') {
        $default_filter_texwjax_pathlatex   = "/usr/bin/latex";
        $default_filter_texwjax_pathdvips   = "/usr/bin/dvips";
        $default_filter_texwjax_pathdvisvgm = "/usr/bin/dvisvgm";
        $default_filter_texwjax_pathconvert = "/usr/bin/convert";
    } else if (PHP_OS=='Darwin') {
        // most likely needs a fink install (fink.sf.net)
        $default_filter_texwjax_pathlatex   = "/sw/bin/latex";
        $default_filter_texwjax_pathdvips   = "/sw/bin/dvips";
        $default_filter_texwjax_pathdvisvgm = "/usr/bin/dvisvgm";
        $default_filter_texwjax_pathconvert = "/sw/bin/convert";

    } else if (PHP_OS=='WINNT' or PHP_OS=='WIN32' or PHP_OS=='Windows') {
        // note: you need Ghostscript installed (standard), miktex (standard)
        // and ImageMagick (install at c:\ImageMagick)
        $default_filter_texwjax_pathlatex   = "c:\\texmf\\miktex\\bin\\latex.exe";
        $default_filter_texwjax_pathdvips   = "c:\\texmf\\miktex\\bin\\dvips.exe";
        $default_filter_texwjax_pathdvisvgm   = "c:\\texmf\\miktex\\bin\\dvisvgm.exe";
        $default_filter_texwjax_pathconvert = "c:\\imagemagick\\convert.exe";
    }

    $pathlatex = get_config('filter_texwjax', 'pathlatex');
    $pathdvips = get_config('filter_texwjax', 'pathdvips');
    $pathconvert = get_config('filter_texwjax', 'pathconvert');
    $pathdvisvgm = get_config('filter_texwjax', 'pathdvisvgm');
    if (strrpos($pathlatex . $pathdvips . $pathconvert . $pathdvisvgm, '"') or
            strrpos($pathlatex . $pathdvips . $pathconvert . $pathdvisvgm, "'")) {
        set_config('pathlatex', trim($pathlatex, " '\""), 'filter_texwjax');
        set_config('pathdvips', trim($pathdvips, " '\""), 'filter_texwjax');
        set_config('pathconvert', trim($pathconvert, " '\""), 'filter_texwjax');
        set_config('pathdvisvgm', trim($pathdvisvgm, " '\""), 'filter_texwjax');
    }

    $items[] = new admin_setting_configexecutable('filter_texwjax/pathlatex', get_string('pathlatex', 'filter_texwjax'), '', $default_filter_texwjax_pathlatex);
    $items[] = new admin_setting_configexecutable('filter_texwjax/pathdvips', get_string('pathdvips', 'filter_texwjax'), '', $default_filter_texwjax_pathdvips);
    $items[] = new admin_setting_configexecutable('filter_texwjax/pathconvert', get_string('pathconvert', 'filter_texwjax'), '', $default_filter_texwjax_pathconvert);
    $items[] = new admin_setting_configexecutable('filter_texwjax/pathdvisvgm', get_string('pathdvisvgm', 'filter_texwjax'), '', $default_filter_texwjax_pathdvisvgm);
    $items[] = new admin_setting_configexecutable('filter_texwjax/pathmimetex', get_string('pathmimetex', 'filter_texwjax'), get_string('pathmimetexdesc', 'filter_texwjax'), '');

    // Even if we offer GIF, PNG and SVG formats here, in the update callback we check whether
    // required paths actually point to executables. If they don't, we force the setting
    // to GIF, as that's the only format mimeTeX can produce.
    $formats = array('gif' => 'GIF', 'png' => 'PNG', 'svg' => 'SVG');
    $items[] = new admin_setting_configselect('filter_texwjax/convertformat', get_string('convertformat', 'filter_texwjax'), get_string('configconvertformat', 'filter_texwjax'), 'png', $formats);

    foreach ($items as $item) {
        $item->set_updatedcallback('filter_texwjax_updatedcallback');
        $settings->add($item);
    }
}
