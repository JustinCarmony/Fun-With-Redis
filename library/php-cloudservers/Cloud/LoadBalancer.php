<?php
/**
 * PHP Cloud Load Balancers implementation for RackSpace (tm)
 * 
 * @package phpCloudServers
 */
/**
 * Load Balancer API implementation
 * 
 * @package phpCloudServers
 */
class Cloud_LoadBalancer extends Cloud {
	
    private $_apiBalancers = array();
    private $_apiNodes = array();
    
    private $_apiNodeCondition = array(
        'ENABLED',
        'DISABLED',
        'DRAINING'
    );
    private $_apiAlgorithms = array(
        'LEAST_CONNECTIONS',
        'RANDOM',
        'ROUND_ROBIN',
        'WEIGHTED_LEAST_CONNECTIONS',
        'WEIGHTED_ROUND_ROBIN'
    );
    private $_apiPersistence = array(
        'HTTP_COOKIE'
    );
	
    /**
     * Lists current Loadbalancers
     *
     * @return mixed json string containing current servers or false on failure
     */
    public function getBalancers ()
    {
        $this->_apiResource = '/loadbalancers.json'; // As of 25/03 API does not default to JSON on this command
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '203')) {
            if (property_exists($this->_apiResponse, 'loadBalancers')) {
                // Reset internal balancer array
                $this->_apiBalancers = array();
                foreach ($this->_apiResponse->loadBalancers as $balancer) {
                    $this->_apiBalancers[(int) $balancer->id]['name'] = (string) $balancer->name;
                }
            }

            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Retrieves configuration details for specific server
     *
     * @return mixed json string containing server details or false on failure
     */
    public function getBalancer ($balancerId)
    {
        $this->_apiResource = '/loadbalancers/'. (int) $balancerId . '.json'; // As of 25/03 API does not default to JSON on this command
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);
        
        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '203')) {
            // Save balancer names to avoid creating duplicate balancers
            if (property_exists($this->_apiResponse, 'loadBalancers')) {
                $this->_apiBalancers[(int) $this->_apiResponse->server->id] =
                    array('id' => (int) $this->_apiResponse->server->id,
                            'name' => (string) $this->_apiResponse->server->name);
            }

            return $this->_apiResponse;
        }

        return false;
    }
	
    /**
     * Adds a node before creating a balancer
     *
     * @param string $address IP Address of the node
     * @param int $port local port on the node
     * @param strong $condition Whether the node is enabled
     */
     public function newNode ($address, $port, $condition = true)
     {
        if($condition) {
                $condition = "ENABLED";
        } else {
                $condition = "DISABLED";
        }
        array_push ($this->_apiNodes, array('address' => $address,
                                            'port' => (string)$port,
                                            'condition' => $condition));
        return $this->_apiNodes;
     }
	

    /**
     * Creates a new load balancer
     *
     * @param string $name balancer name, must be unique
     * @param int $port external facing port balancer will use
     * @param string $protocol protocol for the balancer
     * @param string $virtualIP PUBLIC, SERVICENET or ID of existing IP
     * @param string $algorithm Balancing algorithm to use
     * @param bool $connectionLogging Enable Connection logging
     * @param string $sessionPersistence HTTP_COOKIE is the only currently available option
     * @return mixed returns json string of balancer's configuration or false on failure
     */
    public function createBalancer ($name, $port, $protocol, $virtualIp = "PUBLIC", $algorithm = "RANDOM", $connectionLogging = false, $sessionPersistence = null)
    {
        // We have to have nodes set up to create a balancer
        if (count($this->_apiNodes) == 0) return false;
        
        // Check the provided strings are suitable
        if (!in_array((string) strtoupper($algorithm), $this->_apiAlgorithms)) {
            throw new Cloud_Exception ('Passed algorithm is not supported');
        }
        if ($sessionPersistence != null && !in_array((string) strtoupper($sessionPersistence), $this->_apiPersistence)) {
            throw new Cloud_Exception ('Passed persistence method is not supported');
        }
        if ($sessionPersistence == 'HTTP_COOKIE' && strtoupper($protocol) != 'HTTP') throw new Cloud_Exception ('HTTP_COOKIE persistence can only be used with HTTP protocol');

        // Since Rackspace automaticly removes all spaces/non alpha-numeric characters
        // let's do this on our end before submitting data
        $name = preg_replace("/[^a-zA-Z0-9-]/", '', (string) $name);

        // We need to check if we are creating a duplicate balancer name,
        // since creating two balancers with same name can cause problems.
        $this->getBalancers();

        foreach ($this->_apiBalancers as $balancer) {
            if (strtolower($balancer['name']) == strtolower($name)) {
                throw new Cloud_Exception ('Balancer with name: '. $name .' already exists!');
            }
        }
	
        if ($virtualIp === "PUBLIC" || $virtualIp === "SERVICENET") {
            $Ips = array(array('type' => $virtualIp));
        } else {
            $Ips = array(array('id' => $virtualIp));
        }

        $this->_apiResource = '/loadbalancers';
        $this->_apiJson = array ('loadBalancer' => array(
                                'name' => $name,
                                'port' => (string) $port,
                                'protocol' => $protocol,
				'virtualIps' => $Ips,
                                'algorithm' => $algorithm,
                                'nodes' => $this->_apiNodes));
        
        if ($sessionPersistence != null) {
            array_push($this->_apiJson['loadBalancer'], array('sessionPersistence' => $sessionPersistence));
        }

        $this->_doRequest(self::METHOD_POST, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            //Clear nodes
            $this->_apiNodes = array();
            return $this->_apiResponse;
        }

        return false;
    }

    /**
     * Delete Balancer
     *
     * @param int $balancerId id of balancer you wish to delete
     * @return bool returns true on success or false on fail
     */
    public function deleteBalancer ($balancerId)
    {
        $this->_apiResource = '/loadbalancers/'. (int) $balancerId;
        $this->_doRequest(self::METHOD_DELETE, self::RESOURCE_BALANCER);

        // If balancer was deleted
        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            return true;
        }

        return false;
    }

    /**
     * Get a list of nodes for a particular balancer
     * 
     * @param int $balancerId 
     * @return mixed JSON node details or false
     */
    public function getNodes($balancerId) {
        $this->_apiResource = '/loadbalancers/'.$balancerId.'/nodes.json'; // As of 25/03 API does not default to JSON on this command
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '202')) {
            return $this->_apiResponse;
        }

        return false; 
    }
    
    /**
     * Gets the details for a specific node on a specific balancer
     * 
     * @param int $balancerId
     * @param int $nodeId
     * @return mixed JSON details of specific node or false on error 
     */
    public function getNode($balancerId, $nodeId) {
        $this->_apiResource = '/loadbalancers/'.$balancerId.'/nodes/'.$nodeId.'.json'; // As of 25/03 API does not default to JSON on this command
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200' || $this->_apiResponseCode == '202')) {
            return $this->_apiResponse;
        }

        return false; 
    }
    
    /**
     * Adds a node to a balancer, must use newNode to define the nodes to add
     * 
     * @param int $balanerId ID of the balancer to add nodes to
     * @return mixed JSON response with node details or false
     */
    public function addNode($balanerId) {
        //Must have added nodes using newNode
        if (count($this->_apiNodes) == 0) return false;
        
        $this->_apiResource = '/loadbalancers/'.$balanerId.'/nodes.json'; // As of 25/03 API does not default to JSON on this command
        $this->_apiJson = array ('nodes' => $this->_apiNodes);

        $this->_doRequest(self::METHOD_POST, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && $this->_apiResponseCode == '202') {
            //Clear nodes
            $this->_apiNodes = array();
            return $this->_apiResponse;
        }

        return false;        
    }
    
    
    
    
    /**
     * Retrieves list of virtual IPs on a specific balancer
     *
     * @return mixed returns json string containing details on the Virtual IPs
     * false on failure
     */
    public function getVirtualIPs ($balancerId)
    {
        $this->_apiResource = '/loadbalancers/'. $balancerId . '/virtualips.json'; // As of 25/03 API does not default to JSON on this command
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
           	    || $this->_apiResponseCode == '202')) {
        	return $this->_apiResponse;
        }

        return false;
    }

	/**
     * Retrieves all of the available balancing protocols
     *
     * @return mixed returns json string containing available protocol and ports or
     * false on failure
     */
    public function getProtocols ()
    {
        $this->_apiResource = '/loadbalancers/protocols';
        $this->_doRequest(self::METHOD_GET, self::RESOURCE_BALANCER);

        if ($this->_apiResponseCode && ($this->_apiResponseCode == '200'
           	    || $this->_apiResponseCode == '203')) {
        	return $this->_apiResponse;
        }

        return false;
    }

}