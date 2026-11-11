<?php 
    namespace Metayota;
    include_once('../metayota/library.php');
    include_once('../format-text/format-text.php');
    include_once('../slugify/slugify.php');
    include_once('../translation-service/translation-service.php');
?>
<h1 class="no-print"><t>print_preview</t></h1>
<div class="action-buttons no-print">
    <div>
        <form.button label="<t>convert_sales_document</t>" class="form-button" (click)="this.convert()"></form.button>
        <form.button enabled="{!this.refreshing}" label="<t>refresh</t>" class="form-button" (click)="this.refresh()"></form.button>
        <form.button label="<t>sales_document_export_pdf</t>" class="form-button" (click)="this.exportPdf()"></form.button>
        <div if="this.document && this.document.status != 'sent' && this.documentType?.cover_mail">
            <form.button enabled="{!this.sendingCoverMail}" label="<t>send_via_mail</t>" class="form-button" (click)="this.sendCoverMail()"></form.button>
        </div>
        <form.button  label="<?= translate('edit') ?>"(click)="router$.goto('/edit-sales-document/'+this.document?.id)"></form.button>
    </div>
</div>
 

<div class="no-print">
    <loader loading="{this.refreshing}"></loader>
</div>

<div class="sales-document-detail layout-{this.document?.layout}" if="this.isExport">


    <div class="document-content" if="this.document">
        <div class="two-column">
            <div class="company-header">
                <div class="company-info" if="this.company">
                    <div class="sender-line">{this.company.name_short} ・ {this.company.street} ・
                        {this.company.postal_code} {this.company.city}</div>
                </div>
                <div class="recipient-address" if="this.contact">
                    <div if="this.contact.company">{this.contact.company}</div>
                    <div if="this.contact.department">{this.contact.department}</div>
                    <div if="this.contact.lastname">{this.contact.salutation} {this.contact.firstname} {this.contact.lastname}</div>
                    <div if="this.contact.street">{this.contact.street}</div>
                    <div if="this.contact.zip_code || this.contact.city">{this.contact.zip_code} {this.contact.city}</div>
                </div>
            </div>


            <div class="info-block">
                <div class="info-intro-block">
                    {this.company.phone}<br />
                    {this.company.email}
                </div>
                <div>
                    {this.document.number_name}:
                </div>
                <div>{this.document.document_number}</div>
                <div>
                    <t>sales_document_date</t>:
                </div>
                <div>{this.document.date}</div>
                <div if="this.items.length > 0 && this.document.document_type != 'offer'">
                    <t>sales_document_delivery_date</t>:
                </div>
                <div if="this.items.length > 0 && this.document.document_type != 'offer'">{this.document.delivery_date}
                </div>
                <div if="this.items.length > 0 && this.document.document_type != 'offer'">
                    <t>sales_document_due_date</t>:
                </div>
                <div if="this.items.length > 0 && this.document.document_type == 'offer'">
                    <t>sales_document_valid_until</t>:
                </div>
                <div if="this.items.length > 0">{this.document.due_date}</div>
            </div>
        </div>

        <div class="document-header">
            <p><b>{this.document.title_translated}</b></p>
        </div>

        <div class="document-content">
            <innerhtml value="{this.document.intro_text_formatted}"></innerhtml>
            <innerhtml if="this.document?.body_text_formatted" value="{this.document.body_text_formatted}"></innerhtml>
        </div>

        <table class="items-table" if="this.items.length > 0">
            <thead>
                <tr>
                    <th>
                        <t>sales_document_item_position</t>
                    </th>
                    <th>
                        <t>sales_document_item_description</t>
                    </th>
                    <th if="!this.all_quantity_one">
                        <t>sales_document_item_quantity</t>
                    </th>
                    <th if="!this.all_quantity_one">
                        <t>sales_document_item_unit_price</t>
                    </th>
                    <th if="!this.all_tax_zero">
                        <t>sales_document_item_tax_rate</t>
                    </th>
                    <th>
                        <t>sales_document_item_total</t>
                    </th>
                </tr>
            </thead>
            <tbody for="item of this.items">
                <tr>
                    <td>{item.position}</td>
                    <td><b>{item.description}</b>{item.detail}</td>
                    <td if="!this.all_quantity_one">{item.quantity}</td>
                    <td if="!this.all_quantity_one">{item.unit_price_formatted}</td>
                    <td if="!this.all_tax_zero">{item.tax_rate_formatted}</td>
                    <td>{item.item_total_formatted}</td>
                </tr>
            </tbody>
        </table>

        <div class="totals-grid" if="this.items.length > 0">
            <div class="payment-terms">
                <innerhtml value="{this.document.payment_terms_formatted}"></innerhtml>
            </div>
            <div class="totals-subgrid">
                <div class="totals-row">
                    <div class="totals-label">
                        <t>sales_document_netto</t>
                    </div>
                    <div class="totals-value">{this.totals.net}</div>
                </div>

                <!-- NEW: loop through VAT per tax rate -->
                <div class="totals-row" for="vatRate of this.totals.vat_by_rate">
                    <div class="totals-label">
                        <t>sales_document_vat</t> ({vatRate.rate})
                    </div>
                    <div class="totals-value">{vatRate.vat}</div>
                </div>

                <div class="totals-row total">
                    <div class="totals-label">
                        <t>sales_document_total</t>
                    </div>
                    <div class="totals-value">{this.totals.gross}</div>
                </div>
            </div>
        </div>

        <div class="document-content">
            <innerhtml value="{this.document.bottom_text_formatted}"></innerhtml>
        </div>


    </div>
</div>

<div class="document-actions no-print print-preview" if="this.document && this.document.file">
    <img (load)="this.setAttribute('refreshing', false)" src="/call/image.resize?w=2500&src={this.document.file}"/>
</div>