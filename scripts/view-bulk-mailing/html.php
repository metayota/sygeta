<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../translation-service/translation-service.php');  ?><div class="view-bulk-mailing">
    <h1>{this.mailing?.title_translated}</h1>
    <p><b>
            <t>view_bulk_mailing_title</t>
        </b></p>

    <sales-document-detail-view document_id="{this.mailing?.sales_document_id}"></sales-document-detail-view>

    <h2>
        <t>receivers</t>
    </h2>
    <table class="table colored">
        <tbody for="receiver of this.mailing?.receivers">
            <tr>
                <td>{receiver.full_name}</td>
                <td><a if="!!receiver.file" href="{receiver.file}" target="_blank">
                        <t>download_pdf</t>
                    </a></td>
                <td>
                    <a if="!receiver.sent" (click)="this.markAsSent(receiver,true)"><t>mark_as_sent</t></a>
                    <a if="receiver.sent" (click)="this.markAsSent(receiver, false)">&check; <t>has_been_mailed</t></a>
                </td>
            </tr>
        </tbody>
    </table>
    <div class="button-box">
        <button (click)="this.regenerate()" class="form-button">
            <t>regenerate_pdfs</t>
        </button>
        <button (click)="this.generateZIP()" class="form-button">
            <t>generate_zip</t>
        </button>
    </div>
</div>