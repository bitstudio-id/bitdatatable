<?php

/**
 * This file is part of BITDataTable.
 *
 * (c) 2023 Ibnul Mutaki <ibnuul@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * User: cacing69
 * Date: 13/07/2019
 * Time: 21:42
 */

namespace BITStudio\BITDataTable\Models;


class Column
{
    /**
     * $data : data from request column.
     *
     * @var string
     */
    public $data;

    /**
     * $name : name from request column.
     *
     * @var string
     */
    public $name;

    /**
     * $searchable : searchable from request column.
     *
     * @var bool
     */
    public $searchable;

    /**
     * $orderable : orderable from request column.
     *
     * @var bool
     */
    public $orderable;


    /**
     * get data column nane
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * set data column nane
     *
     * @param string
     */
    public function setData(string $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSearchable()
    {
        return $this->searchable;
    }

    /**
     * @param mixed $searchable
     */
    public function setSearchable(string $searchable)
    {
        $this->searchable = filter_var($searchable, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return mixed
     */
    public function getOrderable()
    {
        return $this->orderable;
    }

    /**
     * @param mixed $orderable
     */
    public function setOrderable(string $orderable)
    {

        $this->orderable = filter_var($orderable, FILTER_VALIDATE_BOOLEAN);
    }


}
