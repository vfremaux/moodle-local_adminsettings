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

function xmldb_local_adminsettings_install() {
    global $DB, $CFG;

    // create the siteadmin role if absent
    if (!$siteadminrole = $DB->get_record('role', array('shortname' => 'siteadmin'))) {
        $siteadminid = create_role(get_string('siteadmin', 'local_adminsettings'), 'siteadmin', str_replace("'", "\\'", get_string('siteadmindesc', 'local_adminsettings')), 'manager');
        set_role_contextlevels($siteadminid, array(CONTEXT_SYSTEM));

        $manager = $DB->get_record('role', array('shortname' => 'manager'));
        role_cap_duplicate($manager, $siteadminid);
    } else {
        $siteadminid = $siteadminrole->id;
    }
    // Allow general config to that role.
    role_change_permission($siteadminid, context_system::instance(), 'moodle/site:config', CAP_ALLOW);
    // Fix sort order. Put site admin as top role by sortorder descending to avoid key collision
    $roles = $DB->get_records('role', array(), 'sortorder DESC');
    foreach ($roles as $r) {
        $DB->set_field('role', 'sortorder', $r->sortorder + 1, array('id' => $r->id));
    }

    $DB->set_field('role', 'sortorder', 1, array('shortname' => 'siteadmin'));

    if (empty($CFG->integratorplugins)) {

        $integratorplugins = 'mod_sharedresource,local_sharedresources,repository_sharedresources,block_sharedresources,block_activity_publisher'.
         ',mod_certificate,mod_learningtimecheck,report_learningtimecheck,block_use_stats,report_trainingsessions,block_learningtimecheck'.
         ',format_page,filter_multilangenhanced,block_page_module,block_page_tracker,mod_pagemenu'.
         ',mod_customlabel'.
         ',enrol_autoenrol,enrol_profilefield,enrol_delayedcohorts'.
         ',local_technicalsignals,local_statigguitexts,local_my'.
         ',block_vmoodle,local_vmoodle,report_vmoodle,block_publishflow,block_user_mnet_hosts'.
         ',block_shop,block_shop_bills,block_shop_products,block_shop_total,auth_ticket'.
         ',mod_mplayer,mod_wowslider,mod_richmedia'.
         ',mod_scheduler,mod_jobtracker'.
         ',mod_referentiel,report_referentiel,block_referentiel'.
         ',filter_oembed,repository_onenote,repository_office365,auth_oidc,block_o365_links,block_onenote,local_o365'.
         ',mod_adobeconnect,mod_elluminate,mod_attendance,mod_bigbluebuttonbn,mod_recordingsbn';

        set_config('integratorplugins', $integratorplugins);
    }

    return true;
}