<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>manage_contacts</t></h1>

<tabs (change)="this.loadContacts(event)" tabs="{this.stepTypes}"></tabs>

<database.table.editor add_link="/create-contact" view_link="/contact-detail/[id]" resourcetype="contact" table="contact" where="{this.where}"></database.table.editor>

<div if="loggedInUser$.usergroup_id == 3">
    <h2><t>admin_functions</t></h2>
    <a if="!this.show_all" (click)="this.showAll()"><t>show_all_contacts</t></a>
    <a if="this.show_all" (click)="this.showMyContacts()"><t>show_my_contacts</t></a>
</div>