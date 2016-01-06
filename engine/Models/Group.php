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
        ['user', 'class_name' => 'User']
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

    public function to_array()
    {
        $array = parent::to_array();
        $array['use_admin'] = $this->can('use_admin');
        $array['users'] = User::count([
            'conditions' => ['group_id = ?', $this->id]
        ]);
        return $array;
    }
}