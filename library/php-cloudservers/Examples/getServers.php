<?php
/**
 * Shows a combined view of US and UK servers with an added "loc" field in the
 * JSON returned
 * 
 * @package phpCloudServers
 * @subpackage examples
 */

require_once('../Cloud/Cloud.php');

DEFINE('UK_API_ID', 'uk_username');
DEFINE('UK_API_KEY', 'APIKEYFROMRACKSPACE');

DEFINE('US_API_ID', 'us_username');
DEFINE('US_API_KEY', 'APIKEYFROMRACKSPACE');

//Create 2 instances of the Cloud_Server object, one for UK auth and one for
//US auth
$cloudUK = new Cloud_Server(UK_API_ID, UK_API_KEY, "UK");
$cloudUS = new Cloud_Server(US_API_ID, US_API_KEY, "US");

try {
    //Get the list of servers and convert JSON string to array
    $resp = json_decode($cloudUK->getServers(true), true);
    //List is contained within the "servers" element
    $serversUK = $resp["servers"];
    //Loop through servers
    foreach ($serversUK as &$server) {
        //Add the location
        $server['loc'] = "uk";
        //Get the JSON information for the image used on the server and add to
        //the existing object
        $server['image'] = json_decode($cloudUK->getImage($server['imageId']), true);
        //Get the flavor (RAM and disk size) of the server and add
        $server['flavor'] = json_decode($cloudUK->getFlavor($server['flavorId']), true);
    }
    unset($server);

    //Do the same for the US list
    $resp = json_decode($cloudUS->getServers(true), true);
    $serversUS = $resp["servers"];
    foreach ($serversUS as &$server) {
        $server['loc'] = "us";
        $server['image'] = json_decode($cloudUS->getImage($server['imageId']), true);
        $server['flavor'] = json_decode($cloudUS->getFlavor($server['flavorId']), true);
    }
    unset($server);

    //Merge the two lists to create a single array
    $servers = array_merge($serversUK, $serversUS);
    //Dump to the browser as JSON
    echo json_encode($servers);

} catch (Cloud_Exception $e) {
	echo $e->getMessage();
}
?>