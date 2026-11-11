<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>choose_payout_method_title</t></h1>

<form.radio value="{this.partnerPayoutMethod?.payout_method_id}" (change)="this.payoutMethodChosen(event)" label="<t>payout_method</t>" options="{this.payoutMethodOptions}"></form.radio>

<form.resource filter_value="{true}" value="{this.partnerPayoutMethod?.payout_data}" (submit)="this.save(event)" submitlabel="<t>save</t>" resourcetype="{this.payoutMethod?.resource_form}"></form.resource>