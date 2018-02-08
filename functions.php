<?php
class Utility {
    private $address = "";  // Address to monitor
    private $balance = 0;   // Balance of address
    private $transactions = "";
    private $recentTransaction = "";
    private $logFile = "log.txt"; // Logging new transactions


    function __construct($address) {
        $this->address = $address;
        $this->update_address();
        $this->check_for_new_transactions(); // Runs to prevent unwanted trigger calls
    }

    /* Update Address
    **
    ** fetch new balance, transactions and most recent transaction
    ** Return: Null
    **
    */
    private function update_address() {
        $response = @file_get_contents('https://explorer.flashcoin.io/api/addr/'.$this->address); // Obtain balance from API
        if(!$response) {
            echo "Invalid Address\n";
            die();
        }
        $response = json_decode($response, True); // Change JSON to array.

        $this->balance += $response['balance']; // Update balance
        $this->transactions = $response['transactions']; // Update transactions list
        $this->recentTransaction = end($response['transactions']); // Most recent transaction
    }

    /* Checking for new transactions
    **
    ** Check for new transactions, update the address and write them to a log file if found
    ** Return: Array[ (string)txid, (double)value ];
    **
    */
    function check_for_new_transactions() {
        $this->update_address();
        $lastTransaction = file_get_contents('lasttransaction'); // Last recorded transaction
        if($this->recentTransaction != $lastTransaction) {
            file_put_contents('lasttransaction', $this->recentTransaction);
            $txValue = $this->get_transaction_value();
            return [
                'txid' => $this->recentTransaction,
                'value' => $txValue,
            ];
        }
    }

    /* Fetch transaction value
    **
    ** Fetch the amount of money that was transfered.
    ** Return: (double)balance;
    **
    */
    function get_transaction_value() {
        $response = file_get_contents('https://explorer.flashcoin.io/api/tx/'.$this->recentTransaction); // Obtain balance from API
        $response = json_decode($response, True);
        foreach($response['vout'] as $txOut) {
            if($txOut['scriptPubKey']['addresses'][0] != $this->address) {
                return $txOut['value'];
            }
        }
    }

    /* Log Transaction to file
    **
    ** Log transaction, date and amount to file
    ** Return: (boolean)
    **
    */
    function log_transaction($transaction) {
        $logTransaction = date("Y-m-d H:i:s")." - Transaction of {$transaction['value']} made.\n";
        file_put_contents($this->logFile, $logTransaction, FILE_APPEND | LOCK_EX);
    }

    /* Fetch Balance of wallet
    **
    ** Return the amount of money in the wallet now.
    ** Return: (double)balance;
    **
    */
    function get_wallet_balance() {
        return $this->balance;
    }
}

?>
