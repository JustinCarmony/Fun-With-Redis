<?php
/**
 * PHP Cloud Server implementation for RackSpace (tm)
 * 
 * @package phpCloudServers
 */

/**
 * Class for API access to Cloud Servers
 */
class Cloud_Server extends Cloud {
	
    private $_apiBackup = array(
        'weekly' => array(
                'DISABLED',
                'SUNDAY',
                'MONDAY',
                'TUESDAY',
                'WEDNESDAY',
                'THURSDAY',
                'FRIDAY',
                'SATURDAY'),
        'daily' => array(
                'DISABLED',
                'H_0000_0200',
                'H_0200_0400',
                'H_0400_0600',
                'H_0600_0800',
                'H_0800_1000',
                'H_1000_1200',
                'H_1400_1600',
                'H_1600_1800',
                'H_1800_2000',
                'H_2000_2200',
                'H_2200_0000'));

    private $_apiServers = array();
    private $_apiFiles = array();

    /**
     * Retrieves details regarding specific server flavor
     *
     * @param int $flavorId id of a flavor you wish to retrieve details for
     * @return mixed returns json string containing details for requested flavor or
     * false on failure
     */
    public function getFlavor ($flavorId)
    {
        $this->_apiResource = '/flavors/'. (int) $flavorId;
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
           	    || $this->_apiResponseCode == '203')) {
        	return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieves all of the available server flavors
     *
     * @return mixed returns json string containing available server configurations or
     * false on failure
     */
    public function getFlavors ($isDetailed = false)
    {
        $this->_apiResource = '/flavors' . ($isDetailed ? '/detail' : '');
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
           	    || $this->_apiResponseCode == '203')) {
        	return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Creates a new image of server
     *
     * @param string $name name of new image
     * @param int $serverId server id for which you wish to base this image on
     * @return mixed returns json string containing details of created image or false on failure
     */
    public function createImage ($name, $serverId)
    {
        $this->_apiResource = '/images';
        $this->_apiJson = array ('image' => array(
                                    'serverId' => (int) $serverId,
                                    'name' => (string) $name));
        $this->_doRequest(self::METHOD_POST);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '200') {
        	return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieves details of specific image
     *
     * @param int $imageId id of image you wish to retrieve details for
     * @return json string containing details of requested image
     */
    public function getImage ($imageId)
    {
        $this->_apiResource = '/images/'. (int) $imageId;
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
            return $this->_apiResponse;
        }
    }

    /**
     * Retrieves all of the available images
     *
     * @return mixed returns json string of available images or false on failure
     */
    public function getImages ($isDetailed = false)
    {
        $this->_apiResource = '/images' . ($isDetailed ? '/detail' : '');
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieves configuration details for specific server
     *
     * @return mixed json string containing server details or false on failure
     */
    public function getServer ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId;
        $this->_doRequest();
        
        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '203')) {
            // Save server names to avoid creating dublicate servers
            if (property_exists($this->_apiResponse, 'server')) {
                $this->_apiServers[(int) $this->_apiResponse->server->id] =
                    array('id' => (int) $this->_apiResponse->server->id,
                            'name' => (string) $this->_apiResponse->server->name);
            }

            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieves currently available servers
     *
     * @return mixed json string containing current servers or false on failure
     */
    public function getServers ($isDetailed = false)
    {
        $this->_apiResource = '/servers'. ($isDetailed ? '/detail' : '');
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '203')) {
            if (property_exists($this->_apiResponse, 'servers')) {
                // Reset internal server array
                $this->_apiServers = array();
                foreach ($this->_apiResponse->servers as $server) {
                    $this->_apiServers[(int) $server->id]['name'] = (string) $server->name;
                }
            }

            return $this->_apiResponse;
        }

        return false;
    }

    public function shareServerIp ($serverId, $serverIp, $groupId, $doConfigure = false)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/ips/public/'. $serverIp;
        $this->_apiJson = array ('shareIp' => array(
                                    'sharedIpGroupId' => (int) $groupId,
                                    'configureServer' => (bool) $doConfigure));
		$this->_doRequest(self::METHOD_PUT);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '201') {
            return true;
        }

        return false;
    }

    /**
     * Removes a shared server IP from server
     * @param int $serverId id of server this action is peformed for
     * @param string $serverIp IP you wish to unshare
     * @return bool returns true on success or false on failure
     */
    public function unshareServerIp ($serverId, $serverIp)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/ips/public/'. (string) $serverIp;
        $this->_doRequest(self::METHOD_DELETE);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Get IP's assigned to server
     *
     * @param int $serverId id of server you wish to retrieve ips for
     * @param string $type type of addresses to retrieve could be private/public or
     * false for both types.
     * @return mixed returns json string of server addresses or false of failure
     */
    public function getServerIp ($serverId, $type = false)
    {
       $this->_apiResource = '/servers/'. (int) $serverId .'/ips'. ($type ? '/'. $type : '');
       $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Add a server to shared ip group
     *
     * @param string $name name of shared ip group you are creating
     * @param int $serverId id of server you wish to add to this group
     * @return mixed returns json string containing id of created shared ip group or false on failure
     */
    public function addSharedIpGroup ($name, $serverId)
    {
        $this->_apiResource = '/shared_ip_groups';
        $this->_apiJson = array ('sharedIpGroup' => array(
                                    'name' => (string) $name,
                                    'server' => (int) $serverId));
        $this->_doRequest(self::METHOD_POST);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '201') {
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Delete shared IP group
     *
     * @param int $groupId id of group you wish to delete
     * @return bool returns true on success and false on failure
     */
    public function deleteSharedIpGroup ($groupId)
    {
        $this->_apiResource = '/shared_ip_groups/'. (int) $groupId;
        $this->_doRequest(self::METHOD_DELETE);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '204') {
            return true;
        }

        return false;
    }

    /**
     * Retrieve details for specific IP group
     *
     * @param int $groupId id of specific shared group you wish to retrieve details
     * for
     * @return mixed returns json string containing details about requested group
     * or false on failure
     */
    public function getSharedIpGroup ($groupId)
    {
        $this->_apiResource = '/shared_ip_groups/'. (int) $groupId;
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieve all the available shared IP groups
     *
     * @param bool $isDetailed should response contain an array of servers group has
     * @return mixed returns json string of groups or false on failure
     */
    public function getSharedIpGroups ($isDetailed = false)
    {
        $this->_apiResource = '/shared_ip_groups'. ($isDetailed ? '/detail' : '');
        $this->_doRequest();

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
        	return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieve back-up schedule for a specific server
     *
     * @param int $serverId id of server you wish to retrieve back-up schedule for
     * @return mixed returns json string of current back-up schedule or false on failure
     */
    public function getBackupSchedule ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/backup_schedule';
        $this->_doRequest();

	    if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
                || $this->_apiResponseCode == '203')) {
            return $this->_apiResponse;
	    }

        return false;
    }

    /**
     * Create a new back-up schedule for a server
     *
     * @param int $serverId id of a server this back-up schedule is intended for
     * @param string $weekly day of the week this back-up should run, please
     * $_apiBackup array and/or documentation for valid parameters.
     * @param string $daily time of the day this back-up should run, please
     * $_apiBackup array and/or documentation for valid parameters.
     * @param bool $isEnabled should this scheduled back-up be enabled or disabled,
     * default is set to enabled.
     * @throws Cloud_Exception
     * @return bool true on success and false on failure
     */
    public function addBackupSchedule ($serverId, $weekly, $daily, $isEnabled = true)
    {
        if (!in_array((string) strtoupper($weekly), $this->_apiBackup['weekly'])) {
            throw new Cloud_Exception ('Passed weekly back-up parameter is not supported');
        }

        if (!in_array((string) strtoupper($daily), $this->_apiBackup['daily'])) {
            throw new Cloud_Exception ('Passed daily back-up parameter is not supported');
        }

        $this->_apiResource = '/servers/'. (int) $serverId .'/backup_schedule';
        $this->_apiJson = array ('backupSchedule' => array(
                                    'enabled' => (bool) $isEnabled,
                                    'weekly' => (string) strtoupper($weekly),
                                    'daily' => (string) strtoupper($daily)));
        $this->_doRequest(self::METHOD_POST);

	    if ($this->_apiResponseCode && $this->_apiResponseCode == '204') {
            return true;
	    }

        return false;
    }

    /**
     * Deletes scheduled back-up for specific server
     *
     * @param int $serverId id of server you wish to delete all scheduled back-ups
     * for
     * @return bool returns true on success or false on failure
     */
    public function deleteBackupSchedule ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/backup_schedule';
        $this->_doRequest(self::METHOD_DELETE);

	    if ($this->_apiResponseCode && $this->_apiResponseCode == '204') {
            return true;
	    }

        return false;
    }

    /**
     * Creates a new server on the cloud
     *
     * @param string $name server name, must be unique
     * @param int $imageId server image you wish to use
     * @param int $flavorId server flavor you wish to use
     * @param int $groupId optional group id of server cluster
     * @return mixed returns json string of server's configuration or false on failure
     */
    public function createServer ($name, $imageId, $flavorId, $groupId = false)
    {
        // Since Rackspace automaticly removes all spaces/non alpha-numeric characters
        // let's do this on our end before submitting data
        $name = preg_replace("/[^a-zA-Z0-9-]/", '', (string) $name);

        // We need to check if we are creating a dublicate server name,
        // since creating two servers with same name can cause problems.
        $this->getServers();

        foreach ($this->_apiServers as $server) {
            if (strtolower($server['name']) == strtolower($name)) {
                throw new Cloud_Exception ('Server with name: '. $name .' already exists!');
            }
        }

        $this->_apiResource = '/servers';
        $this->_apiJson = array ('server' => array(
                                'name' => $name,
                                'imageId' => (int) $imageId,
                                'flavorId' => (int) $flavorId,
                                'metadata' => array(
                                    'Original Name' => $name,
                                    'Creation' => date("F j, Y, g:i a")),
                                'personality' => array()));

        if (is_array($this->_apiFiles) && !empty($this->_apiFiles)) {
            foreach ($this->_apiFiles as $file => $content) {
                array_push($this->_apiJson['server']['personality'],
                   array('path' => $file, 'contents' => base64_encode($content)));
            }
        }

        if (is_numeric($groupId)) {
			$this->_apiJson['server']['sharedIpGroupId'] = (int) $groupId;
        }
		
		echo json_encode($this->_apiJson);

        $this->_doRequest(self::METHOD_POST);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Adds file to inject while creating new server
     *
     * @param string $file full file path where file will be put (/etc/motd,etc)
     * @param string $content content of the file (Welcome to my server, etc)
     * @return array returns array of all files pending injection
     */
    public function addServerFile ($file, $content) {
        $this->_apiFiles[(string) $file] = (string) $content;
        return $this->_apiFiles;
    }

    /**
     * Update server's name and password
     *
     * @param int $serverId id of server you wish to update
     * @param string $name new server name
     * @param string $password new server password
     * @return mixed returns false on failure or server configuration on success
     */
    public function updateServer ($serverId, $name, $password)
    {
        $this->_apiResource = '/servers/'. (int) $serverId;
        $this->_apiJson = array ('server' => array(
                                    'name' => (string) $name,
                                    'adminPass' => (string) $password));
        $this->_doRequest(self::METHOD_PUT);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Delete server
     *
     * @param int $serverId id of server you wish to delete
     * @return bool returns true on success or false on fail
     */
    public function deleteServer ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId;
        $this->_doRequest(self::METHOD_DELETE);

        // If server was deleted
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Rebuild server using another server image
     *
     * @param int $serverId id of server you wish to rebuild
     * @param int $imageId id of server image you wish to use for this rebuild
     * @return bool returns true on success or false on fail
     */
    public function rebuildServer ($serverId, $imageId)
    {
        $this->_apiResource = '/servers/' . (int) $serverId .'/action';
        $this->_apiJson = array ('rebuild' => array(
                                    'imageId' => (int) $imageId));
        $this->_doRequest(self::METHOD_PUT);

        // If rebuild request is successful
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Resize server to another flavor (server configuration)
     *
     * @param int $serverId id of server you wish to resize
     * @return bool returns true on success or false on fail
     */
    public function resizeServer ($serverId, $flavorId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/action';
        $this->_apiJson = array ('resize' => array(
                                    'flavorId' => (int) $flavorId));
        $this->_doRequest(self::METHOD_PUT);

        // If confirmation is successful
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Confirm resize of server
     *
     * @param int $serverId id of server this confirmation is for
     * @return bool returns true on success or false on fail
     */
    public function confirmResize ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/action';
        $this->_apiJson = array ('confirmResize' => '1');
        $this->_doRequest(self::METHOD_PUT);

        // If confirmation is successful
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Revert resize changes
     *
     * @param int $serverId id of server you wish to revert resize for
     * @return bool returns true on success or false on fail
     */
    public function revertResize ($serverId)
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/action';
        $this->_apiJson = array ('revertResize' => '1');
        $this->_doRequest(self::METHOD_PUT);

        // If revert is successful
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Reboots server
     *
     * @param int $serverId id of server you wish to reboot
     * @param string $type specify what kind of reboot you wish to perform
     * @return bool returns true on success or false on fail
     */
    public function rebootServer ($serverId, $type = 'soft')
    {
        $this->_apiResource = '/servers/'. (int) $serverId .'/action';
        $this->_apiJson = array ('reboot' => array(
                                    'type' => (string) strtoupper($type)));
        $this->_doRequest(self::METHOD_POST);

        // If reboot request was successfully recieved
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }
}

/* Legacy Class for upgraders */
if (!class_exists('Cloud')) require_once('Cloud.php');