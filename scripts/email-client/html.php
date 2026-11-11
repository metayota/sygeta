<?php namespace Metayota; include_once('../metayota/library.php');   include_once('../gemini-api/gemini-api.php');   include_once('../php-mailer/php-mailer.php');   include_once('../translation-service/translation-service.php');  ?><div class="mail-client-box">
    <choose-mail-account (loaded)="this.accountsLoaded(event)" (change)="this.account_id = event;"></choose-mail-account>
    <p if="this.noAccounts" class="no-accounts-added">
        <t>no_accounts_added</t>
    </p>
    <div class="email-client" if="!this.noAccounts">
        <div class="sidebar">
            <div class="folders" for="folder of this.folders">
                <div class="folder-item {this.selectedFolder?.name == folder.name ? 'selected' : ''}"
                    (click)="this.selectFolder(folder)">
                    {folder.name} ({folder.message_count})
                </div>
            </div>
        </div>

        <div class="email-list">
            <div class="email-items" for="email of this.emails">
                <div class="email-item {(!email.is_read ? 'unread' : '')+' '+ (this.selected == email.message_num ? 'mail-selected' : '')}"
                    (click)="this.selectEmail(email)">
                    <div class="email-sender">{email.from}</div>
                    <div class="email-subject">{email.subject}</div>
                    <div class="email-date">{email.date}</div>
                    <div if="email.has_attachments" class="attachment-indicator">
                        <img src="/images/icons/file-clip.svg">
                    </div>
                </div>
            </div>

            <paginator (change)="this.updatePage(event)" number_of_pages="{this.pagination?.total_pages}"
                page="{this.pagination?.current_page}"></paginator>

            <loader loading="{this.loading}"></loader>

            <div if="this.error" class="error-message">
                <t>email_load_error</t>
            </div>

        </div>

        <div class="email-detail" if="this.selectedEmail">
            <div class="mail-content" if="this.replyTo">
                <mail-sender (sent)="this.setAttribute('replyTo',undefined)" is_html="{true}"
                    message="{this.replyTo.content ? this.replyTo.content.quoted_html : this.selectedEmail.content.quoted_html}"
                    subject="{this.replyTo.subject}" recipient="{ this.currentEmail.reply_to_formatted }"></mail-sender>
            </div>
            <div if="!this.replyTo">
                <div class="button-header">
                    <img (click)="this.replyToMail(this.selectedEmail)" src="/images/icons/mail/reply.svg">
                    <img (click)="this.forwardEmail(this.selectedEmail)" src="/images/icons/mail/forward.svg">
                    <img (click)="this.markAsSpam(this.selectedEmail)" src="/images/icons/mail/spam.svg">
                    <img (click)="this.deleteSelectedEmail()" src="/images/icons/mail/trash.svg">
                    <!--img (click)="this.replyToMail(this.selectedEmail);this.aiSuggestion(this.selectedEmail)"
                        src="/images/icons/mail/bot.svg"-->
                </div>

                <div class="email-header">
                    <h2>{this.selectedEmail.subject}</h2>
                    <div class="email-meta">
                        <div class="sender">
                            <b>
                                <t>from</t>
                            </b> {this.selectedEmail.from.name} &lt;{this.selectedEmail.from.email}&gt;<br />
                            <b>
                                <t>reply_to</t>
                            </b> {this.currentEmail.reply_to_formatted}<br />
                            <b>
                                <t>to</t>
                            </b> {this.currentEmail.to_formatted}
                        </div>
                        <div class="date">{this.selectedEmail.received_date}</div>
                    </div>
                </div>

                <div class="email-content">
                    <iframe
                        (load)="node.style.height =( node.contentWindow.document.documentElement.scrollHeight + 30) + 'px';"
                        style="width:100%;height:100%;" srcdoc="{this.selectedEmail.content.html}"></iframe>
                </div>

                <div class="attachments" if="this.selectedEmail.attachments" for="attachment of this.selectedEmail.attachments">
                    <div class="attachment" 
                        (click)="this.downloadAttachment(attachment)">
                        {attachment.filename}
                    </div>
                </div>
            </div>
        </div>
    </div>