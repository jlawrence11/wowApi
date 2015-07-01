<?php
/**
 * Created by: Jon Lawrence on 2015-06-30 12:45 PM
 */

namespace jlawrence\wowApi;

class Items implements \ArrayAccess {
    private $container = array();
    private $site;
    public function __construct(FactoryLw $site) {
        // Initialized!
        $this->site = $site;
    }

    public function offsetSet($offset, $value) {
        throw new \Exception("wowItems is a read only class, cannot set $offset to $value");
    }

    public function offsetGet($offset) {
        if(!isset($this->container[$offset])) {
            // no item currently retrieved, let's make sure we get it
            $this->container[$offset] = new ItemCache($this->site);
            $this->container[$offset]->get($offset);
        }
        return $this->container[$offset];
    }

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        throw new \Exception("Items is a read only class, cannot unset $offset");
    }
}