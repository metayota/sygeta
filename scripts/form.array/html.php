<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="form-array">
    <label if="this.label !== undefined" class="form-label">{translate(this.label)}</label>
    <table if="this._value != null && this._value.length > 0" class="table" >
        <thead>
            <tr if="this.resource != undefined" for="parameter of this.resource.parameters">
                <th>{translate(parameter.title)}</td>
                <th last><?= translate('actions') ?></th>
            </tr>
        </thead>
        <tbody for="item of this._value">
            <tr if="this.resource != undefined" (dragleave)="this.leaveDroparea(event,node)" (dragover)="this.allowDrop(event,node)" class="tr-droparea"     (drop)="this.drop(event, item_index)" draggable="true" (dragstart)="this.drag(event, item_index)" for="parameter of this.resource.parameters">
                <td><object.viewparameter showlabel="{false}" parameter="{parameter}" value="{item[parameter.name]}"></object.viewparameter></td>
                <td last class="action-icons">
                    <span (click)="this.editItem(item_index)"><img class="icon" src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/edit.svg"/></span>
                    <span (click)="this.deleteItem(item_index)"><img class="icon" src="<?= getConfig('wordpress') ? '/wp-content/plugins/metayota/editor_scripts' : '/scripts' ?>/rc.icon/delete.svg"/></span>
                </td>
            </tr>
        </tbody>
    </table>

    <a (click)="this.addItemDialog()"><?= translate('add_item') ?></a>

</div>