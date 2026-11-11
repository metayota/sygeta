<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><breadcrumbs></breadcrumbs>
<h1><t>manage_step_action_title</t></h1>
<form.resource element="stepActionEl" resource_name="step_action" value="{this.stepAction}"></form.resource>
<dropdown (change)="this.templateChanged(event)" options="{this.stepActionTemplates}" label="<t>step_action_template_label</t>" value="{this.stepAction?.step_action_template}"></dropdown>
<form.resource element="params" resource_name="{this.stepActionTemplate?.form_resource}" value="{this.stepAction?.parameters}"></form.resource>
<form.button label="<t>save</t>" (click)="this.save()"></form.button>