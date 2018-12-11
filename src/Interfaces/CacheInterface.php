<?php
/**
 * CacheInterface interface required by CacheHealthCheckInterface
 */

namespace Giffgaff\ServiceHealthCheck\Interfaces;

interface CacheInterface
{
    public function save($result, $id);
    public function load($id);
    public function remove($id);
}
