<?php namespace Metayota; include_once('../metayota/library.php');  ?><div>
    <router base="/<?= $GLOBALS['language'] ?>">
        <routes array="">
            <route url="/">
                <content tag="home"></content>
            </route>
            <route url="/page/[page]" title="metayota.com">
                <content tag="website.view"></content>
            </route>
            <route url="/account">
                <content tag="website.account"></content>
            </route>
            <route url="/create/bulk_mailing" return_path="/view-bulk-mailing/[id]" resource_name="bulk_mailing">
                <content tag="create-db-row" ></content>
            </route>
            <route url="/invitation/[event_name]/[partner_code]">
                <content tag="event-invitation"></content>
            </route>
            <route url="/event-invitation/[event_name]/[invitation_code]">
                <content tag="event-invitation"></content>
            </route>
            <route url="/create-step-action/[step_type_id]">
                <content tag="manage-step-action"></content>
            </route>
            <route url="/event-invitation/[event_name]">
                <content tag="event-invitation"></content>
            </route>
            <route url="/create/[resource_name]">
                <content tag="create-db-row"></content>
            </route>
            <route url="/book-meeting/[meeting_type]/[meeting_invitation_id]">
                <content tag="book-meeting"></content>
            </route>
           <route url="/plan-meeting/[meeting_type]/[contact_id]">
                <content tag="plan-meeting"></content>
            </route>
            <route url="/contact-detail/[contact_id]">
                <content tag="contact-card"></content>
            </route>
            <route url="/data-editor/[table]">
                <content tag="data-editor"></content>
            </route>
            <route url="/edit-data/[resourcetype]/[rowid]">
                <content tag="row-data-editor"></content>
            </route>

            <route url="/page/[resource]/[tab]" resource="website.view"></route>
            <route url="/[page]/[tab]/[id]"> 
                <content tag="show-page">
                </content>
            </route>
            <route url="/[page]/[tab]"> 
                <content tag="show-page">
                </content>
            </route>
            <route url="/[page]">
                <content tag="show-page">
                </content>
            </route>

        </routes>
    </router>
</div>