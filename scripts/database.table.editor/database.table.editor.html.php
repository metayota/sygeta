<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="database-editor card">
    <h1 if="!!this.title">{this.title}</h1>
    <div if="this.search === undefined || this.search === true">
        <form.text placeholder="<?= translate('search') ?>" (change)="this.searchChanged(event)"></form.text>
    </div>
    <div style="overflow-x: scroll;max-width: 100%;">
        <p if="this.objs and this.objs.length == 0">
            <?= translate('no_items_in_this_view') ?>
        </p>
        <table if="this.objs and this.objs.length > 0" class="table wp-list-table widefat fixed striped table-view-list">
            <thead>
                <tr if="this.resource != undefined" for="parameter of this.resource.parameters">
                    <th first if="!this.read_only">
                        <?= translate('db_editor_actions') ?>
                    </th>
                    <th if="!this.isHidden(parameter)" (click)="this.sortBy(parameter.name)">{translate(parameter.title)}
                        {this.getSortIndicator(parameter.name)}</th>
                </tr>
            </thead>
            <tbody for="obj of this.objs">
                <tr if="this.resource != undefined" for="parameter of this.resource.parameters">
                    <td first if="!this.read_only">
                        <div class="db-editor-row-actions">
                            <i if="this.view_link" (click)="router$.goto(this.view_link.replace(/\[id\]/g, obj.id))" class="action icon-button"><img class="icon"
                                src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/view.svg" /></i>
                        <i if="this.edit_link" (click)="this.editRow( obj )" class="action icon-button"><img class="icon"
                                src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/edit.svg" /></i>
                        <i if="this.delete_link" (click)="this.deleteRow( obj.id )" class="action icon-button"><img class="icon"
                                src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/delete.svg" /></i>
                        </div>
                    </td>
                    <td if="!this.isHidden(parameter)">
                        <object.viewparameter object_id="{obj.id}" showlabel="{false}" parameter="{parameter}" value="{obj[parameter.name]}" value_resolved="{obj[parameter.name+'_resolved']}" value_translated="{obj[parameter.name+'_translated']}">
                        </object.viewparameter>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="tablenav bottom">
        <paginator number_of_pages="{this.number_of_pages}" (change)="this.pageChanged(event)"></paginator>
        <form.button (click)="this.showAddDialog()" label="<?= translate('add') ?>"><form.button>
    </form.button>
</div>