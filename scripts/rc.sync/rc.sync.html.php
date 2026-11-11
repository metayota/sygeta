<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../sync-functions/sync-functions.php');   include_once('../translation-service/translation-service.php');  ?><div class="sync-resources">
    <h1><?= translate('synchronize_resources') ?></h1>
    <p><?= translate('synchronize_resources_description') ?></p>
    <when test="{router$.attributes.subpage}" then="this.subpage = value"></when>
    <dropdown (change)="this.chooseRepository(event)" options="{this.repositoryOptions}" value="{this.repository}"></dropdown>
    <p><a (click)="this.addRepository()"><?= translate('add_repository') ?></a> - <a link="/editor/view/manage-repositories"><t>manage_repositories</t></a></p>
    <p if="this.resources && this.resources.length == 0">&check; <t>all_resources_up_to_date</t></p>
    <table if="this.resources && this.resources.length > 0" class="table">
        <thead>
            <tr>
                <th><?= translate('name') ?></th>
                <th><?= translate('version') ?></th>
                <th><?= translate('remote_version') ?></th>
                <th><?= translate('status') ?></th>
                <th><?= translate('actions') ?></th>
            </tr>
        </thead>
        <tbody for="resource of this.resources">
            <tr class="{resource.status == 'different' ? 'resource-updated' : ''}">
                <td>{resource.name}</td>
                <td>{resource.version != resource.remote_version ? resource.version : resource.last_update}</td>
                <td>{resource.version != resource.remote_version ? resource.remote_version : resource.remote_last_update}</td>
                <td><span if="resource.status != 'different'">{translate(resource.status)}</span> <a if="resource.status == 'different'" link="/editor/compare-to-repository/{this.repository}/{resource.name}"><?= translate('source_different') ?></a> </td>
                <td>
                    <div style="display:flex;gap:10px">
                    <a (click)="this.syncSingleFile(resource.name)" if="resource.status == 'different'"><?= translate('update') ?></a>
                    <a (click)="this.setPrivate(resource.name)" if="resource.status == 'different'"><?= translate('sync_ignore') ?></a>
                    <a (click)="this.syncSingleFile(resource.name)" if="resource.status == 'new'"> <?= translate('download') ?></a>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <form.button (click)="this.updateFromServer()" label="<?= translate('download_all_resources') ?>"></form.button>
</div>