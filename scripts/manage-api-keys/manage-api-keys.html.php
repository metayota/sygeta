<?php 
    namespace Metayota;
    include_once('../translation-service/translation-service.php');
?>
<div class="page manage-api-keys">
    <h1><t>manage_api_keys_title</t></h1>

    <?= translate('manage_api_keys_description') ?>

    <form.radio (change)="this.switchUserSelect(event)" label="<t>manage_api_keys_user_select</t>" options="{ManageAPIKeys.user_options}"></form.radio>

    <div if="this.selectedUserOption == 'create_user'">
        <form.resource element="createUserForm" (submit)="this.generateKey(event)" submitlabel="<t>generate_api_key</t>" resource_name="manage-api-keys"></form.resource>
    </div>

    <div if="this.selectedUserOption == 'existing_user'">
        <div class="select-user">
            <form.options.dbrow element="userSelectEl" label="<t>user</t>" (change)="this.userSelected(event)" dbtable="user" titlefield="username" idfield="id"></form.options.dbrow>
            <view.text label="{this.selectedUserUsergroup ? '<t>usergroup</t>' : ''}" value="{this.selectedUserUsergroup}"></view.text>
        </div>
        <form.button enabled="{this.selectedUserUsergroup}" (click)="this.generateKey({})" label="<t>generate_api_key</t>"></form.button>
    </div>

    <h2><t>existing_api_keys_title</t></h2>

    <p if="this.existingKeys && this.existingKeys.length == 0"><t>no_api_keys_added_yet</t></p>

    <table if="this.existingKeys && this.existingKeys.length > 0" class="table">
        <thead>
            <tr>
                <th><t>username</t></th>
                <th><t>usergroup</t></th>
                <th><t>api_key</t></th>
                <th><t>actions</t></th>
            </tr>
        </thead>
        <tbody for="existingKey of this.existingKeys">
            <tr>
                <td>{existingKey.username}</td>
                <td>{existingKey.usergroup}</td>
                <td>{existingKey.api_key}</td>
                <td><a class="form-button small-button" (click)="this.deleteAPIKey(existingKey)"><t>delete</t></a></td>
            </tr>
        </tbody>
    </table>
</div>