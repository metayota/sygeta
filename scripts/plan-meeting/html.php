<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../contacts-lib/contacts-lib.php');   include_once('../translation-service/translation-service.php');  ?><div if="this.meetingType" class="plan-meeting">
    <h1>{this.meetingType.title}</h1>

    <p>
       <b><t>send_meeting_invitation_to</t>:</b><br/> {this.contact?.full_name} ({this.contact?.email}) 
    </p>

    <h1>{this.formTitle}</h1>
    <p>{this.formDescription}</p>
    <form.resource (submit)="this.sendInvitation(event)" submitlabel="<?= translate('send_meeting_invitation_btn') ?>" resource_name="{this.meetingType.meeting_form}"></form.resource>
</div>