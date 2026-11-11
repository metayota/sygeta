<?php namespace Metayota; include_once('../metayota/library.php');  ?><h1><t>transactions</t></h1>
<table if="this.transactions?.length > 0" class="table wp-list-table widefat fixed striped table-view-list">
  <thead>
    <tr>
      <th><?= translate('resource_transactions_title_amount') ?></th>
      <th><?= translate('resource_transactions_title_reason') ?></th>
      <th><?= translate('resource_transactions_title_date') ?></th>
    </tr>
  </thead>
  <tbody for="transaction of this.transactions">
    <tr  class="{transaction.type}">
      <td class="amount {transaction.type}">{transaction.amount}</td>
      <td>{transaction.reason}</td>
      <td>{new Date(transaction.date).toLocaleDateString()}</td>
    </tr>
  </tbody>  
</table>
<p if="this.transactions?.length == 0"><t>no_transactions_yet</t></p>