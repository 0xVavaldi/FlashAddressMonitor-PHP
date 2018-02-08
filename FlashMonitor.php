<?php
ini_set('max_execution_time', 0); // Prevent PHP aborting execution
// ignore_user_abort(true);

require_once('functions.php');
$Util = new Utility('Address'); // Address to monitor

while(1) {
    if ($transaction = $Util->check_for_new_transactions()) {
        Trigger($transaction);
        $Util->log_transaction($transaction);
    }
    usleep(500000); // Check for new transactions every 0.5 seconds
}

/* Trigger
**
** This runs when a new transaction has been detected.
**
*/
function Trigger($transaction) {
    $value = round($transaction['value'], 2);
    echo "New transaction detected. Value: {$value}\n";
}
?>
