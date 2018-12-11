<?php
/**
 * CacheInterface interface required by CacheHealthCheck
 */

namespace Giffgaff\ServiceHealthCheck\Interfaces;

interface CacheInterface
{
    public function save($result, $id);
    public function load($id);
    public function remove($id);
}
