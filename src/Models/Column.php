<?php
/**
 * Created by PhpStorm.
 * User: Cacing
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
    public function getData() : string
    {
        return $this->data;
    }

    /**
     * set data column nane
     *
     * @param string
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getName() :string
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getSearchable() :bool
    {
        return $this->searchable;
    }

    /**
     * @param mixed $searchable
     */
    public function setSearchable(string $searchable): void
    {
        $this->searchable = filter_var($searchable, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return mixed
     */
    public function getOrderable() :bool
    {
        return $this->orderable;
    }

    /**
     * @param mixed $orderable
     */
    public function setOrderable(string $orderable): void
    {

        $this->orderable = filter_var($orderable, FILTER_VALIDATE_BOOLEAN);
    }


}
