<?php
/**
 * Lists all domains on an account as JSON
 * 
 * @package phpCloudServers
 * @subpackage examples
 */

require_once('../Cloud/Cloud.php');

DEFINE('US_API_ID', 'us_username');
DEFINE('US_API_KEY', 'APIKEYFROMRACKSPACE');

$cloud = new Cloud_DNS(US_API_ID, US_API_KEY, "US");

try {
    //Get a list of all domains on the account and send as JSON
    echo $cloud->getDomains();
} catch (Cloud_Exception $e) {
    echo $e->getMessage();
}
?>