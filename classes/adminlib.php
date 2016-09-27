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

defined('MOODLE_INTERNAL') || die;

class local_admin_settings_manageformats {
    
    /**
     * clone from adminlib.php but taking over
     * Return XHTML to display control
     *
     * @param mixed $data Unused
     * @param string $query
     * @return string highlight
     */
    public static function output_html($data, $query='') {
        global $CFG, $OUTPUT;

        $strhidelocked = get_string('hidelocked', 'local_adminsettings');
        $strshowlocked = get_string('showlocked', 'local_adminsettings');

        $return = '';
        $return = $OUTPUT->heading(new lang_string('courseformats'), 3, 'main');
        $return .= $OUTPUT->box_start('generalbox formatsui');

        $formats = core_plugin_manager::instance()->get_plugins_of_type('format');

        // display strings
        $txt = get_strings(array('settings', 'name', 'enable', 'disable', 'up', 'down', 'default'));
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');
        $txt->updown = "$txt->up/$txt->down";

        $table = new html_table();
        $table->head  = array($txt->name, $txt->enable, $txt->updown, $txt->uninstall, $txt->settings);
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->attributes['class'] = 'manageformattable generaltable admintable';
        $table->data  = array();

        $cnt = 0;
        $defaultformat = get_config('moodlecourse', 'format');
        $spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'iconsmall'));
        foreach ($formats as $format) {
            // CHANGE check if hyperadmin 
            $canadministrate = has_capability('local/adminsettings:nobody', context_system::instance()) || empty($CFG->integratorplugins) || 
                !in_array('format_'.$format->name, explode(',', $CFG->integratorplugins));

            $url = new moodle_url('/admin/courseformats.php',
                    array('sesskey' => sesskey(), 'format' => $format->name));
            $isdefault = '';
            $class = '';

            if ($canadministrate) {
                if ($format->is_enabled()) {
                    $strformatname = $format->displayname;
                    if ($defaultformat === $format->name) {
                        $hideshow = $txt->default;
                    } else {
                        $hideshow = html_writer::link($url->out(false, array('action' => 'disable')),
                                $OUTPUT->pix_icon('t/hide', $txt->disable, 'moodle', array('class' => 'iconsmall')));
                    }
                } else {
                    $strformatname = $format->displayname;
                    $class = 'dimmed_text';
                    $hideshow = html_writer::link($url->out(false, array('action' => 'enable')),
                        $OUTPUT->pix_icon('t/show', $txt->enable, 'moodle', array('class' => 'iconsmall')));
                }
            } else {
                $strformatname = $format->displayname;
                if ($format->is_enabled()) {
                    if ($defaultformat === $format->name) {
                        $hideshow = $txt->default;
                    } else {
                        $hideshow = '<img src="'.$OUTPUT->pix_url('lockedvisible', 'local_adminsettings').'" title="'.$strshowlocked.'" class="" />';
                    }
                } else {
                    $class = 'dimmed_text';
                    $hideshow = '<img src="'.$OUTPUT->pix_url('lockedhidden', 'local_adminsettings').'" title="'.$strhidelocked.'" class="icondimmed" />';
                }
            }

            $updown = '';
            if ($cnt) {
                $updown .= html_writer::link($url->out(false, array('action' => 'up')),
                    $OUTPUT->pix_icon('t/up', $txt->up, 'moodle', array('class' => 'iconsmall'))). '';
            } else {
                $updown .= $spacer;
            }

            if ($cnt < count($formats) - 1) {
                $updown .= '&nbsp;'.html_writer::link($url->out(false, array('action' => 'down')),
                    $OUTPUT->pix_icon('t/down', $txt->down, 'moodle', array('class' => 'iconsmall')));
            } else {
                $updown .= $spacer;
            }
            $cnt++;

            $settings = '';
            if ($canadministrate) {
                if ($format->get_settings_url()) {
                    $settings = html_writer::link($format->get_settings_url(), $txt->settings);
                }
            } else {
                if ($format->get_settings_url()) {
                    if ($format->is_enabled()) {
                        $settings = html_writer::link($format->get_settings_url(), $txt->settings);
                    } else {
                        $settings = $txt->settings;
                    }
                }
            }

            $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('format_'.$format->name, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $txt->uninstall);
            }

            $row = new html_table_row(array($strformatname, $hideshow, $updown, $uninstall, $settings));

            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $link = html_writer::link(new moodle_url('/admin/settings.php', array('section' => 'coursesettings')), new lang_string('coursesettings'));
        $return .= html_writer::tag('p', get_string('manageformatsgotosettings', 'admin', $link));
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}

class local_admin_settings_manageauths {
    /**
     * Return XHTML to display control
     *
     * @param mixed $data Unused
     * @param string $query
     * @return string highlight
     */
    public static function output_html($data, $query='') {
        global $CFG, $OUTPUT, $DB;

        $strhidelocked = get_string('hidelocked', 'local_adminsettings');
        $strshowlocked = get_string('showlocked', 'local_adminsettings');

        // display strings
        $txt = get_strings(array('authenticationplugins', 'users', 'administration',
            'settings', 'edit', 'name', 'enable', 'disable',
            'up', 'down', 'none', 'users'));
        $txt->updown = "$txt->up/$txt->down";
        $txt->uninstall = get_string('uninstallplugin', 'core_admin');
        $txt->testsettings = get_string('testsettings', 'core_auth');

        $authsavailable = core_component::get_plugin_list('auth');
        get_enabled_auth_plugins(true); // fix the list of enabled auths
        if (empty($CFG->auth)) {
            $authsenabled = array();
        } else {
            $authsenabled = explode(',', $CFG->auth);
        }

        // construct the display array, with enabled auth plugins at the top, in order
        $displayauths = array();
        $registrationauths = array();
        $registrationauths[''] = $txt->disable;
        $authplugins = array();
        foreach ($authsenabled as $auth) {

            $authplugin = get_auth_plugin($auth);
            $authplugins[$auth] = $authplugin;
            /// Get the auth title (from core or own auth lang files)
            $authtitle = $authplugin->get_title();
            /// Apply titles
            $displayauths[$auth] = $authtitle;
            if ($authplugin->can_signup()) {
                $registrationauths[$auth] = $authtitle;
            }
        }

        foreach ($authsavailable as $auth => $dir) {
            if (array_key_exists($auth, $displayauths)) {
                continue; //already in the list
            }
            $authplugin = get_auth_plugin($auth);
            $authplugins[$auth] = $authplugin;
            /// Get the auth title (from core or own auth lang files)
            $authtitle = $authplugin->get_title();
            /// Apply titles
            $displayauths[$auth] = $authtitle;
            if ($authplugin->can_signup()) {
                $registrationauths[$auth] = $authtitle;
            }
        }

        $return = $OUTPUT->heading(get_string('actauthhdr', 'auth'), 3, 'main');
        $return .= $OUTPUT->box_start('generalbox authsui');

        $table = new html_table();
        $table->head  = array($txt->name, $txt->users, $txt->enable, $txt->updown, $txt->settings, $txt->testsettings, $txt->uninstall);
        $table->colclasses = array('leftalign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->data  = array();
        $table->attributes['class'] = 'admintable generaltable';
        $table->id = 'manageauthtable';

        //add always enabled plugins first
        $displayname = $displayauths['manual'];
        $settings = "<a href=\"auth_config.php?auth=manual\">{$txt->settings}</a>";
        //$settings = "<a href=\"settings.php?section=authsettingmanual\">{$txt->settings}</a>";
        $usercount = $DB->count_records('user', array('auth'=>'manual', 'deleted'=>0));
        $table->data[] = array($displayname, $usercount, '', '', $settings, '', '');
        $displayname = $displayauths['nologin'];
        $settings = "<a href=\"auth_config.php?auth=nologin\">{$txt->settings}</a>";
        $usercount = $DB->count_records('user', array('auth'=>'nologin', 'deleted'=>0));
        $table->data[] = array($displayname, $usercount, '', '', $settings, '', '');


        // iterate through auth plugins and add to the display table
        $updowncount = 1;
        $authcount = count($authsenabled);
        $url = "auth.php?sesskey=" . sesskey();
        foreach ($displayauths as $auth => $name) {

            // CHANGE check if hyperadmin 
            $canadministrate = has_capability('local/adminsettings:nobody', context_system::instance()) || empty($CFG->integratorplugins) || 
                !in_array('auth_'.$auth, explode(',', $CFG->integratorplugins));

            if ($auth == 'manual' or $auth == 'nologin') {
                continue;
            }
            $class = '';
            // hide/show link
            if (in_array($auth, $authsenabled)) {
                if ($canadministrate) {
                    $hideshow = "<a href=\"$url&amp;action=disable&amp;auth=$auth\">";
                    $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/hide') . "\" class=\"iconsmall\" alt=\"disable\" /></a>";
                } else {
                    $hideshow = '<img src="'.$OUTPUT->pix_url('lockedvisible', 'local_adminsettings').'" class="" title="'.$strshowlocekd.'" />';
                }
                // $hideshow = "<a href=\"$url&amp;action=disable&amp;auth=$auth\"><input type=\"checkbox\" checked /></a>";
                $enabled = true;
                $displayname = $name;
            } else {
                if ($canadministrate) {
                    $hideshow = "<a href=\"$url&amp;action=enable&amp;auth=$auth\">";
                    $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/show') . "\" class=\"iconsmall\" alt=\"enable\" /></a>";
                } else {
                    $hideshow = '<img src="'.$OUTPUT->pix_url('lockedhidden', 'local_adminsettings').'" class="icondimmed" title="'.$strhidelocked.'" />';
                }
                // $hideshow = "<a href=\"$url&amp;action=enable&amp;auth=$auth\"><input type=\"checkbox\" /></a>";
                $enabled = false;
                $displayname = $name;
                $class = 'dimmed_text';
            }

            $usercount = $DB->count_records('user', array('auth' => $auth, 'deleted' => 0));

            // up/down link (only if auth is enabled)
            $updown = '';
            if ($enabled) {
                if ($updowncount > 1) {
                    $updown .= "<a href=\"$url&amp;action=up&amp;auth=$auth\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/up') . "\" alt=\"up\" class=\"iconsmall\" /></a>&nbsp;";
                }
                else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />&nbsp;";
                }
                if ($updowncount < $authcount) {
                    $updown .= "<a href=\"$url&amp;action=down&amp;auth=$auth\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/down') . "\" alt=\"down\" class=\"iconsmall\" /></a>";
                }
                else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />";
                }
                ++ $updowncount;
            }

            // settings link
            if($canadministrate) {
                if (file_exists($CFG->dirroot.'/auth/'.$auth.'/settings.php')) {
                    $settings = "<a href=\"settings.php?section=authsetting$auth\">{$txt->settings}</a>";
                } else {
                    $settings = "<a href=\"auth_config.php?auth=$auth\">{$txt->settings}</a>";
                }
            } else {
                $settings = $txt->settings;
            }

            // Uninstall link.
            $uninstall = '';
            if($canadministrate) {
                if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('auth_'.$auth, 'manage')) {
                    $uninstall = html_writer::link($uninstallurl, $txt->uninstall);
                }
            }

            $test = '';
            if (!empty($authplugins[$auth]) and method_exists($authplugins[$auth], 'test_settings')) {
                $testurl = new moodle_url('/auth/test_settings.php', array('auth'=>$auth, 'sesskey'=>sesskey()));
                $test = html_writer::link($testurl, $txt->testsettings);
            }

            // Add a row to the table.
            $row = new html_table_row(array($displayname, $usercount, $hideshow, $updown, $settings, $test, $uninstall));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= get_string('configauthenticationplugins', 'admin').'<br />'.get_string('tablenosave', 'filters');
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}


class local_admin_settings_manageenrols {
    /**
     * Builds the XHTML to display the control
     *
     * @param string $data Unused
     * @param string $query
     * @return string
     */
    public static function output_html($data, $query='') {
        global $CFG, $OUTPUT, $DB, $PAGE;

        // Display strings.
        $strhidelocked = get_string('hidelocked', 'local_adminsettings');
        $strshowlocked = get_string('showlocked', 'local_adminsettings');
        $strup        = get_string('up');
        $strdown      = get_string('down');
        $strsettings  = get_string('settings');
        $strenable    = get_string('enable');
        $strdisable   = get_string('disable');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strusage     = get_string('enrolusage', 'enrol');
        $strversion   = get_string('version');
        $strtest      = get_string('testsettings', 'core_enrol');

        $pluginmanager = core_plugin_manager::instance();

        $enrols_available = enrol_get_plugins(false);
        $active_enrols    = enrol_get_plugins(true);

        $allenrols = array();
        foreach ($active_enrols as $key=>$enrol) {
            $allenrols[$key] = true;
        }
        foreach ($enrols_available as $key=>$enrol) {
            $allenrols[$key] = true;
        }
        // Now find all borked plugins and at least allow then to uninstall.
        $condidates = $DB->get_fieldset_sql("SELECT DISTINCT enrol FROM {enrol}");
        foreach ($condidates as $candidate) {
            if (empty($allenrols[$candidate])) {
                $allenrols[$candidate] = true;
            }
        }

        $return = $OUTPUT->heading(get_string('actenrolshhdr', 'enrol'), 3, 'main', true);
        $return .= $OUTPUT->box_start('generalbox enrolsui');

        $table = new html_table();
        $table->head  = array(get_string('name'), $strusage, $strversion, $strenable, $strup.'/'.$strdown, $strsettings, $strtest, $struninstall);
        $table->colclasses = array('leftalign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign');
        $table->id = 'courseenrolmentplugins';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data  = array();

        // Iterate through enrol plugins and add to the display table.
        $updowncount = 1;
        $enrolcount = count($active_enrols);
        $url = new moodle_url('/admin/enrol.php', array('sesskey'=>sesskey()));
        $printed = array();
        foreach($allenrols as $enrol => $unused) {

            $canadministrate = has_capability('local/adminsettings:nobody', context_system::instance()) || empty($CFG->integratorplugins) || 
                !in_array('enrol_'.$enrol, explode(',', $CFG->integratorplugins));

            $plugininfo = $pluginmanager->get_plugin_info('enrol_'.$enrol);
            $version = get_config('enrol_'.$enrol, 'version');
            if ($version === false) {
                $version = '';
            }

            if (get_string_manager()->string_exists('pluginname', 'enrol_'.$enrol)) {
                $name = get_string('pluginname', 'enrol_'.$enrol);
            } else {
                $name = $enrol;
            }
            // Usage.
            $ci = $DB->count_records('enrol', array('enrol'=>$enrol));
            $cp = $DB->count_records_select('user_enrolments', "enrolid IN (SELECT id FROM {enrol} WHERE enrol = ?)", array($enrol));
            $usage = "$ci / $cp";

            // Hide/show links.
            $class = '';
            if (isset($active_enrols[$enrol])) {
                if ($canadministrate) {
                    $aurl = new moodle_url($url, array('action'=>'disable', 'enrol'=>$enrol));
                    $hideshow = "<a href=\"$aurl\">";
                    $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/hide') . "\" class=\"iconsmall\" alt=\"$strdisable\" /></a>";
                } else {
                    $hideshow = '<img src="'.$OUTPUT->pix_url('lockedvisible', 'local_adminsettings').'" class="" title="'.$strshowlocked.'" />';
                }
                $enabled = true;
                $displayname = $name;
            } else if (isset($enrols_available[$enrol])) {
                if ($canadministrate) {
                    $aurl = new moodle_url($url, array('action'=>'enable', 'enrol'=>$enrol));
                    $hideshow = "<a href=\"$aurl\">";
                    $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/show') . "\" class=\"iconsmall\" alt=\"$strenable\" /></a>";
                } else {
                    $hideshow = '<img src="'.$OUTPUT->pix_url('lockedhidden', 'local_adminsettings').'" class="icondimmed" title="'.$strhidelocked.'" />';
                }
                $enabled = false;
                $displayname = $name;
                $class = 'dimmed_text';
            } else {
                $hideshow = '';
                $enabled = false;
                $displayname = '<span class="notifyproblem">'.$name.'</span>';
            }
            if ($PAGE->theme->resolve_image_location('icon', 'enrol_' . $name, false)) {
                $icon = $OUTPUT->pix_icon('icon', '', 'enrol_' . $name, array('class' => 'icon pluginicon'));
            } else {
                $icon = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'icon pluginicon noicon'));
            }

            // Up/down link (only if enrol is enabled).
            $updown = '';
            if ($enabled) {
                if ($updowncount > 1) {
                    $aurl = new moodle_url($url, array('action'=>'up', 'enrol'=>$enrol));
                    $updown .= "<a href=\"$aurl\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/up') . "\" alt=\"$strup\" class=\"iconsmall\" /></a>&nbsp;";
                } else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />&nbsp;";
                }
                if ($updowncount < $enrolcount) {
                    $aurl = new moodle_url($url, array('action'=>'down', 'enrol'=>$enrol));
                    $updown .= "<a href=\"$aurl\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/down') . "\" alt=\"$strdown\" class=\"iconsmall\" /></a>";
                } else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />";
                }
                ++$updowncount;
            }

            // Add settings link.
            if (!$version) {
                $settings = '';
            } else if ($surl = $plugininfo->get_settings_url()) {
                if ($canadministrate) {
                    $settings = html_writer::link($surl, $strsettings);
                } else {
                    $settings = $strsettings;
                }
            } else {
                $settings = '';
            }

            // Add uninstall info.
            $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('enrol_'.$enrol, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

            $test = '';
            if (!empty($enrols_available[$enrol]) and method_exists($enrols_available[$enrol], 'test_settings')) {
                $testsettingsurl = new moodle_url('/enrol/test_settings.php', array('enrol'=>$enrol, 'sesskey'=>sesskey()));
                $test = html_writer::link($testsettingsurl, $strtest);
            }

            // Add a row to the table.
            $row = new html_table_row(array($icon.$displayname, $usage, $version, $hideshow, $updown, $settings, $test, $uninstall));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;

            $printed[$enrol] = true;
        }

        $return .= html_writer::table($table);
        $return .= get_string('configenrolplugins', 'enrol').'<br />'.get_string('tablenosave', 'admin');
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}