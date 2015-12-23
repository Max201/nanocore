<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */
use ActiveRecord\Model;


/**
 * Class Group
 * @package Entity
 */
class Group extends Model
{
    static $has_many = array(
        array('user', 'class_name' => 'User')
    );

    /**
     * @return Permission
     */
    public function getPermissions()
    {
        return GroupPermission::getByGroup($this);
    }

    /**
     * @param $permission
     * @return bool
     */
    public function can($permission)
    {
        return (bool)$this->getPermissions()[$permission];
    }
}