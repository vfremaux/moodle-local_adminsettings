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

if (!$hassiteconfig) {
    $ADMIN->add('appearance', new admin_category('customisablethemes', new lang_string('themes')));
    // "themesettings" settingpage
    $temp = new admin_settingpage('customthemesettings', new lang_string('themesettings', 'admin'), 'local/adminsettings:configure');
    $ADMIN->add('customisablethemes', $temp);

    $ADMIN->add('appearance', new admin_category('themes', new lang_string('themes')));

    // settings for each theme
    foreach (get_plugin_list('theme') as $theme => $themedir) {
        $settings_path = "$themedir/settings.php";
        if (file_exists($settings_path)) {
            $settings_capabilities = "$themedir/db/access.php";
            if (file_exists($settings_capabilities)) {
                $settings = new admin_settingpage('customthemesetting'.$theme, new lang_string('pluginname', 'theme_'.$theme), "theme/$theme:configure");
            } else {
                $settings = new admin_settingpage('customthemesetting'.$theme, new lang_string('pluginname', 'theme_'.$theme));
            }
            include($settings_path);
            if ($settings) {
                $ADMIN->add('themes', $settings);
            }
        }
    }


    // calendar
    $temp = new admin_settingpage('customisablecalendar', new lang_string('calendarsettings','admin'), 'local/adminsettings:configure');
    $temp->add(new admin_setting_special_adminseesall());
    //this is hacky because we do not want to include the stuff from calendar/lib.php
    $temp->add(new admin_setting_configselect('calendar_site_timeformat', new lang_string('pref_timeformat', 'calendar'),
                                              new lang_string('explain_site_timeformat', 'calendar'), '0',
                                              array('0'        => new lang_string('default', 'calendar'),
                                                    '%I:%M %p' => new lang_string('timeformat_12', 'calendar'),
                                                    '%H:%M'    => new lang_string('timeformat_24', 'calendar'))));
    $temp->add(new admin_setting_configselect('calendar_startwday', new lang_string('configstartwday', 'admin'), new lang_string('helpstartofweek', 'admin'), 0,
    array(
            0 => new lang_string('sunday', 'calendar'),
            1 => new lang_string('monday', 'calendar'),
            2 => new lang_string('tuesday', 'calendar'),
            3 => new lang_string('wednesday', 'calendar'),
            4 => new lang_string('thursday', 'calendar'),
            5 => new lang_string('friday', 'calendar'),
            6 => new lang_string('saturday', 'calendar')
        )));
    $temp->add(new admin_setting_special_calendar_weekend());
    $options = array();
    for ($i=1; $i<=99; $i++) {
        $options[$i] = $i;
    }
    $temp->add(new admin_setting_configselect('calendar_lookahead',new lang_string('configlookahead','admin'),new lang_string('helpupcominglookahead', 'admin'),21,$options));
    $options = array();
    for ($i=1; $i<=20; $i++) {
        $options[$i] = $i;
    }
    $temp->add(new admin_setting_configselect('calendar_maxevents',new lang_string('configmaxevents','admin'),new lang_string('helpupcomingmaxevents', 'admin'),10,$options));
    $temp->add(new admin_setting_configcheckbox('enablecalendarexport', new lang_string('enablecalendarexport', 'admin'), new lang_string('configenablecalendarexport','admin'), 1));

    // Calendar custom export settings.
    $days = array(365 => new lang_string('numdays', '', 365),
            180 => new lang_string('numdays', '', 180),
            150 => new lang_string('numdays', '', 150),
            120 => new lang_string('numdays', '', 120),
            90  => new lang_string('numdays', '', 90),
            60  => new lang_string('numdays', '', 60),
            30  => new lang_string('numdays', '', 30),
            5  => new lang_string('numdays', '', 5));
    $temp->add(new admin_setting_configcheckbox('calendar_customexport', new lang_string('configcalendarcustomexport', 'admin'), new lang_string('helpcalendarcustomexport','admin'), 1));
    $temp->add(new admin_setting_configselect('calendar_exportlookahead', new lang_string('configexportlookahead','admin'), new lang_string('helpexportlookahead', 'admin'), 365, $days));
    $temp->add(new admin_setting_configselect('calendar_exportlookback', new lang_string('configexportlookback','admin'), new lang_string('helpexportlookback', 'admin'), 5, $days));
    $temp->add(new admin_setting_configtext('calendar_exportsalt', new lang_string('calendarexportsalt','admin'), new lang_string('configcalendarexportsalt', 'admin'), random_string(60)));
    $temp->add(new admin_setting_configcheckbox('calendar_showicalsource', new lang_string('configshowicalsource', 'admin'), new lang_string('helpshowicalsource','admin'), 1));
    $ADMIN->add('appearance', $temp);

    // blog
    $temp = new admin_settingpage('customizableblog', new lang_string('blog','blog'), 'local/adminsettings:configure', empty($CFG->enableblogs));
    $temp->add(new admin_setting_configcheckbox('useblogassociations', new lang_string('useblogassociations', 'blog'), new lang_string('configuseblogassociations','blog'), 1));
    $temp->add(new admin_setting_bloglevel('bloglevel', new lang_string('bloglevel', 'admin'), new lang_string('configbloglevel', 'admin'), 4, array(BLOG_GLOBAL_LEVEL => new lang_string('worldblogs','blog'),
                                                                                                                                           BLOG_SITE_LEVEL => new lang_string('siteblogs','blog'),
                                                                                                                                           BLOG_USER_LEVEL => new lang_string('personalblogs','blog'))));
    $temp->add(new admin_setting_configcheckbox('useexternalblogs', new lang_string('useexternalblogs', 'blog'), new lang_string('configuseexternalblogs','blog'), 1));
    $temp->add(new admin_setting_configselect('externalblogcrontime', new lang_string('externalblogcrontime', 'blog'), new lang_string('configexternalblogcrontime', 'blog'), 86400,
        array(43200 => new lang_string('numhours', '', 12),
              86400 => new lang_string('numhours', '', 24),
              172800 => new lang_string('numdays', '', 2),
              604800 => new lang_string('numdays', '', 7))));
    $temp->add(new admin_setting_configtext('maxexternalblogsperuser', new lang_string('maxexternalblogsperuser','blog'), new lang_string('configmaxexternalblogsperuser', 'blog'), 1));
    $temp->add(new admin_setting_configcheckbox('blogusecomments', new lang_string('enablecomments', 'admin'), new lang_string('configenablecomments', 'admin'), 1));
    $temp->add(new admin_setting_configcheckbox('blogshowcommentscount', new lang_string('showcommentscount', 'admin'), new lang_string('configshowcommentscount', 'admin'), 1));
    $ADMIN->add('appearance', $temp);

    // Navigation settings
    $temp = new admin_settingpage('customisablenavigation', new lang_string('navigation'), 'local/adminsettings:configure');
    $choices = array(
        HOMEPAGE_SITE => new lang_string('site'),
        HOMEPAGE_MY => new lang_string('mymoodle', 'admin'),
        HOMEPAGE_USER => new lang_string('userpreference', 'admin')
    );
    $temp->add(new admin_setting_configselect('defaulthomepage', new lang_string('defaulthomepage', 'admin'), new lang_string('configdefaulthomepage', 'admin'), HOMEPAGE_SITE, $choices));
    $temp->add(new admin_setting_configcheckbox('allowguestmymoodle', new lang_string('allowguestmymoodle', 'admin'), new lang_string('configallowguestmymoodle', 'admin'), 1));
    $temp->add(new admin_setting_configcheckbox('navshowfullcoursenames', new lang_string('navshowfullcoursenames', 'admin'), new lang_string('navshowfullcoursenames_help', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('navshowcategories', new lang_string('navshowcategories', 'admin'), new lang_string('confignavshowcategories', 'admin'), 1));
    $temp->add(new admin_setting_configcheckbox('navshowmycoursecategories', new lang_string('navshowmycoursecategories', 'admin'), new lang_string('navshowmycoursecategories_help', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('navshowallcourses', new lang_string('navshowallcourses', 'admin'), new lang_string('confignavshowallcourses', 'admin'), 0));
    $sortoptions = array(
        'sortorder' => new lang_string('sort_sortorder', 'admin'),
        'fullname' => new lang_string('sort_fullname', 'admin'),
        'shortname' => new lang_string('sort_shortname', 'admin'),
        'idnumber' => new lang_string('sort_idnumber', 'admin'),
    );
    $temp->add(new admin_setting_configselect('navsortmycoursessort', new lang_string('navsortmycoursessort', 'admin'), new lang_string('navsortmycoursessort_help', 'admin'), 'sortorder', $sortoptions));
    $temp->add(new admin_setting_configtext('navcourselimit',new lang_string('navcourselimit','admin'),new lang_string('confignavcourselimit', 'admin'),20,PARAM_INT));
    $temp->add(new admin_setting_configcheckbox('usesitenameforsitepages', new lang_string('usesitenameforsitepages', 'admin'), new lang_string('configusesitenameforsitepages', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('linkadmincategories', new lang_string('linkadmincategories', 'admin'), new lang_string('linkadmincategories_help', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('navshowfrontpagemods', new lang_string('navshowfrontpagemods', 'admin'), new lang_string('navshowfrontpagemods_help', 'admin'), 1));
    $temp->add(new admin_setting_configcheckbox('navadduserpostslinks', new lang_string('navadduserpostslinks', 'admin'), new lang_string('navadduserpostslinks_help', 'admin'), 1));

    $ADMIN->add('appearance', $temp);

    // "htmlsettings" settingpage
    $temp = new admin_settingpage('customisablehtmlsettings', new lang_string('htmlsettings', 'admin'), 'local/adminsettings:configure');
    $temp->add(new admin_setting_configcheckbox('formatstringstriptags', new lang_string('stripalltitletags', 'admin'), new lang_string('configstripalltitletags', 'admin'), 1));
    $temp->add(new admin_setting_emoticons());
    $ADMIN->add('appearance', $temp);
    $ADMIN->add('appearance', new admin_externalpage('customisableresetemoticons', new lang_string('emoticonsreset', 'admin'),
        new moodle_url('/admin/resetemoticons.php'), 'local/adminsettings:configure', true));


    // The "media" subpage.
    $temp = new admin_settingpage('customisablemediasettings', get_string('mediasettings', 'core_media'), 'local/adminsettings:configure');

    $temp->add(new admin_setting_heading('mediaformats', get_string('mediaformats', 'core_media'),
            format_text(get_string('mediaformats_desc', 'core_media'), FORMAT_MARKDOWN)));

    // External services.
    $temp->add(new admin_setting_configcheckbox('core_media_enable_youtube',
            get_string('siteyoutube', 'core_media'), get_string('siteyoutube_desc', 'core_media'), 1));
    $temp->add(new admin_setting_configcheckbox('core_media_enable_vimeo',
            get_string('sitevimeo', 'core_media'), get_string('sitevimeo_desc', 'core_media'), 0));

    // Options which require Flash.
    $temp->add(new admin_setting_configcheckbox('core_media_enable_mp3',
            get_string('mp3audio', 'core_media'), get_string('mp3audio_desc', 'core_media'), 1));
    $temp->add(new admin_setting_configcheckbox('core_media_enable_flv',
            get_string('flashvideo', 'core_media'), get_string('flashvideo_desc', 'core_media'), 1));
    $temp->add(new admin_setting_configcheckbox('core_media_enable_swf',
            get_string('flashanimation', 'core_media'), get_string('flashanimation_desc', 'core_media'), 1));

    // HTML 5 media.
    // Audio now enabled by default so that it can provide a fallback for mp3 on devices without flash.
    $temp->add(new admin_setting_configcheckbox('core_media_enable_html5audio',
            get_string('html5audio', 'core_media'), get_string('html5audio_desc', 'core_media'), 1));
    // Video now enabled by default so it can provide mp4 support.
    $temp->add(new admin_setting_configcheckbox('core_media_enable_html5video',
            get_string('html5video', 'core_media'), get_string('html5video_desc', 'core_media'), 1));

    // Legacy players.
    $temp->add(new admin_setting_heading('legacymediaformats',
            get_string('legacyheading', 'core_media'), get_string('legacyheading_desc', 'core_media')));

    $temp->add(new admin_setting_configcheckbox('core_media_enable_qt',
            get_string('legacyquicktime', 'core_media'), get_string('legacyquicktime_desc', 'core_media'), 1));
    $temp->add(new admin_setting_configcheckbox('core_media_enable_wmp',
            get_string('legacywmp', 'core_media'), get_string('legacywmp_desc', 'core_media'), 1));
    $temp->add(new admin_setting_configcheckbox('core_media_enable_rm',
            get_string('legacyreal', 'core_media'), get_string('legacyreal_desc', 'core_media'), 1));

    $ADMIN->add('appearance', $temp);


    // "documentation" settingpage
    $temp = new admin_settingpage('customisabledocumentation', new lang_string('moodledocs'), 'local/adminsettings:configure');
    $temp->add(new admin_setting_configtext('docroot', new lang_string('docroot', 'admin'), new lang_string('configdocroot', 'admin'), 'http://docs.moodle.org', PARAM_URL));
    $temp->add(new admin_setting_configcheckbox('doctonewwindow', new lang_string('doctonewwindow', 'admin'), new lang_string('configdoctonewwindow', 'admin'), 0));
    $ADMIN->add('appearance', $temp);

    $temp = new admin_externalpage('customisablemypage', new lang_string('mypage', 'admin'), $CFG->wwwroot . '/my/indexsys.php', 'local/adminsettings:configure');
    $ADMIN->add('appearance', $temp);

    $temp = new admin_externalpage('customisableprofilepage', new lang_string('myprofile', 'admin'), $CFG->wwwroot . '/user/profilesys.php', 'local/adminsettings:configure');
    $ADMIN->add('appearance', $temp);

    // coursecontact is the person responsible for course - usually manages enrolments, receives notification, etc.
    $temp = new admin_settingpage('customisablecoursecontact', new lang_string('courses'), 'local/adminsettings:configure');
    $temp->add(new admin_setting_special_coursecontact());
    $temp->add(new admin_setting_configcheckbox('courselistshortnames',
            new lang_string('courselistshortnames', 'admin'),
            new lang_string('courselistshortnames_desc', 'admin'), 0));
    $temp->add(new admin_setting_configtext('coursesperpage', new lang_string('coursesperpage', 'admin'), new lang_string('configcoursesperpage', 'admin'), 20, PARAM_INT));
    $temp->add(new admin_setting_configtext('courseswithsummarieslimit', new lang_string('courseswithsummarieslimit', 'admin'), new lang_string('configcourseswithsummarieslimit', 'admin'), 10, PARAM_INT));
    $temp->add(new admin_setting_configtext('courseoverviewfileslimit', new lang_string('courseoverviewfileslimit'),
            new lang_string('configcourseoverviewfileslimit', 'admin'), 1, PARAM_INT));
    $temp->add(new admin_setting_configtext('courseoverviewfilesext', new lang_string('courseoverviewfilesext'),
            new lang_string('configcourseoverviewfilesext', 'admin'), '.jpg,.gif,.png'));
    $ADMIN->add('appearance', $temp);

    // link to tag management interface
    $ADMIN->add('appearance', new admin_externalpage('customisablemanagetags', new lang_string('managetags', 'tag'), "$CFG->wwwroot/tag/manage.php", 'local/adminsettings:configure'));

    $temp = new admin_settingpage('customisableadditionalhtml', new lang_string('additionalhtml', 'admin'), 'local/adminsettings:configure');
    $temp->add(new admin_setting_heading('additionalhtml_heading', new lang_string('additionalhtml_heading', 'admin'), new lang_string('additionalhtml_desc', 'admin')));
    $temp->add(new admin_setting_configtextarea('additionalhtmlhead', new lang_string('additionalhtmlhead', 'admin'), new lang_string('additionalhtmlhead_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtextarea('additionalhtmltopofbody', new lang_string('additionalhtmltopofbody', 'admin'), new lang_string('additionalhtmltopofbody_desc', 'admin'), '', PARAM_RAW));
    $temp->add(new admin_setting_configtextarea('additionalhtmlfooter', new lang_string('additionalhtmlfooter', 'admin'), new lang_string('additionalhtmlfooter_desc', 'admin'), '', PARAM_RAW));
    $ADMIN->add('appearance', $temp);
}