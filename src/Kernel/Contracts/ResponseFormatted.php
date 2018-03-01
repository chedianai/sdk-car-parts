<?php
/**
 * Created by PhpStorm.
 * User: keal
 * Date: 2018/3/1
 * Time: 下午12:43
 */

namespace CarParts\Kernel\Contracts;

/**
 * Interface ResponseFormatted
 * @package CarParts\Kernel\Contracts
 */
interface ResponseFormatted
{
    /**
     * Get the instance to format response.
     *
     * @return array
     */
    public function format();
}