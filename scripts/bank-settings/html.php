<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><h1><t>bank_settings_title</t></h1>
<p><t>bank_settings_description</t></p>

<form.radio value="{this.companyPaymentMethod?.payment_method}" (change)="this.paymentMethodChosen(event)" label="<t>payment_method</t>" options="{this.paymentMethodOptions}"></form.radio>

<form.resource filter_value="{true}" value="{this.companyPaymentMethod?.payment_method_data}" (submit)="this.save(event)" submitlabel="<t>save</t>" resourcetype="{this.paymentMethod?.resource_form}"></form.resource>