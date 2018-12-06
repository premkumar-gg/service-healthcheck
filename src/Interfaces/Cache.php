<?php
/**
 * Cache interface required by CacheHealthCheck
 */

namespace Giffgaff\ServiceHealthCheck\Interfaces;

interface Cache
{
    public function save($result, $id);
    public function load($id);
    public function remove($id);
}
