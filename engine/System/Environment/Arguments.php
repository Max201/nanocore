<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Environment;


/**
 * Class Arguments
 * @package System\Environment
 */
class Arguments extends Options
{
    /**
     * @param array $values
     */
    public function values($values = [])
    {
        $keys = array_keys($this->getArrayCopy());
        for($i = 0; $i < count($values); $i++) {
            if ( isset($keys[$i]) ) {
                $this->offsetSet($keys[$i], $values[$i]);
            }
        }
    }
} 