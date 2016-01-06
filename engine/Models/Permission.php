<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */


/**
 * Class Permission
 * @package Entity
 */
class Permission extends \ArrayObject
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @param Group $group
     * @param array $groupPermissions
     */
    public function __construct(Group $group, array $groupPermissions = array())
    {
        $this->group = $group;
        $this->exchangeArray($groupPermissions);
    }

    /**
     * @param string $methodName
     * @param array $args
     * @return bool
     */
    public function __call($methodName, array $args = array())
    {
        if ( strpos($methodName, 'is_') == 0 ) {
            $methodName = substr($methodName, 3);
        }

        return $this->get($methodName);
    }

    /**
     * @return bool
     */
    public function save()
    {
        $perms = GroupPermission::getPermissionsMap();
        $perms->map[$this->group->id] = $this->getArrayCopy();

        return $perms->save();
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function get($key, $default = false)
    {
        if ( parent::offsetExists($key) ) {
            return parent::offsetGet($key);
        }

        return $default;
    }

    /**
     * @param mixed $index
     * @return bool|mixed
     */
    public function offsetGet($index)
    {
        return $this->get($index);
    }

    /**
     * @param mixed $index
     * @param null $newval
     */
    public function offsetSet($index, $newval = null)
    {
        if ( !isset($this[$index]) ) {
            user_error('Unable to set new permission by model', E_USER_WARNING);
            return;
        }

        parent::offsetSet($index, $newval);
    }

    /**
     * @param mixed $index
     */
    public function offsetUnset($index)
    {
        if ( !isset($this[$index]) ) {
            return;
        }

        user_error('Unable to set new permission by model', E_USER_WARNING);
    }

    /**
     * @param mixed $value
     */
    public function append($value)
    {
        user_error('Unable to set new permission by model', E_USER_WARNING);
    }
} 