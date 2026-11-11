<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><breadcrumbs></breadcrumbs>
<h1><t>manage_step_type_title</t></h1>
<form.resource element="stepTypeForm" resource_name="step_type" value="{this.stepType}"></form.resource>
<dropdown (change)="this.templateChanged(event)" label="<t>step_type_template_label</t>" options="{this.stepTypeTemplateOptions}" value="{this.stepType?.step_type_template}"></dropdown>
<form.resource element="params" resource_name="{this.stepType?.step_type_template_obj?.resource_name}" value="{this.stepType?.step_type_template_params}"></form.resource>
<form.button (click)="this.saveStepType()" label="<t>save</t>"></form.button>

<h2><t>manage_step_actions</t></h2>
<p><t>manage_step_actions_description</t></p>

<database.table.editor where="{{step_type_id:this.stepTypeID}}" table="step_action" resourcetype="step_action" edit_link="/manage-step-action/[id]" add_link="/create-step-action/{this.stepType.id}"></database.table.editor>