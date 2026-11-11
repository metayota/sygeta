<?php namespace Metayota; include_once('../metayota/library.php');  ?><table>
  <thead>
    <tr>
      <th><?= translate('resource_transactions_title_amount') ?></th>
      <th><?= translate('resource_transactions_title_reason') ?></th>
    </tr>
  </thead>
  <tbody>
    <tr for="transaction of this.transactions">
      <td>{transaction.amount}</td>
      <td>{transaction.reason}</td>
    </tr>
  </tbody>  
</table>