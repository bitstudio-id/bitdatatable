<?php
/**
 * Created by PhpStorm.
 * User: Cacing
 * Date: 12/07/2019
 * Time: 15:00
 */

namespace BITStudio\BITDataTable;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BITDataTable
{
    private $data;

    /**
     * DataTables request object.
     *
     * @var Illuminate\Http\Request
     */
    private $request;

    /**
     * $query : holder for count data.
     *
     * @var Builder
     */
    private $q;

    /**
     * $query : holder for get data.
     *
     * @var Builder
     */
    private $query;

    /**
     * $query : holder for filtered data.
     *
     * @var Builder
     */
    private $qFilter;

    /**
     * $rowIndex : index identifier on response
     *
     * @var string
     */
    private $rowIndex;

    /**
     * $column : array column holder, based on $request
     *
     * @var array
     */
    private $column;

    /**
     * $mapColumn : mapping custom column from controller
     *
     * @var array
     */
    private $mapColumn;

    /**
     * $closure : custom column handler
     *
     * @var \Closure
     */
    private $closure;

    /**
     * $showTotal : show count all data
     *
     * @var int
     */
    private $showTotal;

    /**
     * $showFiltered : show count filtered data
     *
     * @var int
     */
    private $showFiltered;

    /**
     * $rowClass : add row class response
     *
     * @var ...string
     */
    private $rowClass;

    /**
     * $rowClassName : row class name on response
     *
     * @var string
     */
    private $rowClassName;

    /**
     * $rowId : add row id response
     *
     * @var string
     */
    private $rowId;

    /**
     * $rowIdName : row id name on response
     *
     * @var string
     */
    private $rowIdName;

    /**
     * $rowId : add row id custom response
     *
     * @var \Closure
     */
    private $rowIdClosure;

    /**
     * $rowIndexname : add row index response
     *
     * @var \Closure
     */
    private $rowIndexName;

    /**
     * $searchMode : type of searching
     * have : %string%
     * start : string%
     * end : %string
     * equal : = string
     *
     * @var string
     */
    private $searchMode;

    public function __construct()
    {
        $this->mapColumn = [];
        $this->rowClass = [];
        $this->data = [];
        $this->showTotal = true;
        $this->showFiltered = false;
        $this->rowIndex = false;
        $this->rowIndexName = "DT_RowIndex";
        $this->rowIdName = "DT_RowId";
        $this->rowClassName = "DT_RowClass";
        $this->searchMode = "start";
    }

    public function getRowIndexName()
    {
        return $this->rowIndexName;
    }

    /*
     * DEFAULT CONSTRUCTOR IS EMPTY
     * string $name
     * */
    public function setRowIndexName($name)
    {
        $this->rowIndexName = $name;
    }

    /*
     *
     * */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->column = $this->request->columns;
        return $this;
    }

    public function mapColumn(array $data = [])
    {

        $this->mapColumn = $data;
        return $this;
    }

    public function addCol(\Closure $callback)
    {
        $this->closure = $callback;
        return $this;
    }

    public function addClass(...$string)
    {
        $this->rowClass = $string;

        return $this;
    }

    public function from($query)
    {
        if ($query instanceof QueryBuilder) {
            $this->query = $query;
        } else if ($query instanceof EloquentBuilder) {
            $this->query = $query;
        } else {
            dd("Class not support");
        }

        return $this;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getOrderAbleColumn()
    {
        return array_filter($this->column , function ($item) {
            if (filter_var($item["orderable"] , FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
            return false;
        });
    }

    public function generate()
    {
        $data = $this->closure == null ? $this->getData() : $this->getData()->map($this->closure);

        /*ADD ROW CLASS*/
        if(!empty($this->rowClass)) {
            $rowClass = $this->rowClass;
            $rowClassName = $this->rowClassName;
            $data->map(function ($item) use ($rowClass, $rowClassName) {
                $item->{$rowClassName} = implode(" ", $rowClass);
                return $item;
            });
        }

        if($this->rowIdClosure !== null) {
            $data->map($this->rowIdClosure);
        } elseif ($this->rowId !== null) {
//            dd("A");
            $rowId = $this->rowId;
            $rowIdName = $this->rowIdName;

//            dd($rowId, $rowIdName);
            $data->map(function($item) use ($rowId, $rowIdName){
//                dd($rowId);
                $item->{$rowIdName} = $item->{$rowId};
                return $item;
            });
        }

        if ($this->getRowIndex()) {
            $i = 1;
            $this->request->length = !is_null($this->request->length) ? $this->request->length : 20;
        	$this->request->start = !is_null($this->request->start) ? $this->request->start : 0;
            foreach ($data as $key => $item) {
                $data[$key]->{$this->rowIndexName} = (($this->request->start / $this->request->length) * $this->request->length) + $i;
                $i++;
            }
        }

        $recordsTotal = $this->getTotalDataByQuery($this->getSql($this->q));
        $recordsFiltered = $this->getShowFiltered() ? $this->getTotalDataByQuery($this->getSql($this->qFilter)) : $recordsTotal;

        if(env("APP_DEBUG")) {
            $response["request"] = $this->request->all();
            $response["query"] = $this->getSql($this->query);
        }

        $response["data"] = $data;
        $response["draw"] = $this->request->draw;
        $response["recordsTotal"] = $recordsTotal;
        $response["recordsFiltered"] = $recordsFiltered;

        return $response;
    }

    public function getData()
    {
        if ($this->getShowFiltered()) {
            $this->q = clone $this->query;
        }
        $req = $this->request;
        try {
            if (sizeof($this->getSearchAbleColumn()) > 0) {
                // foreach ($this->getSearchAbleColumn() as $key => $col) {
                //    /*
                //     * MAPPING DARI var : $mapColumn
                //     * */
                //    if(!true) {

                //    } else {
                //        $sourCol = !is_null($col["name"]) ? $col["name"] : $col["data"];
                //        if(!is_null($this->request->search['value'])) {
                //            $this->query->orWhere($sourCol, 'like', $this->request->search['value']."%");
                //        }
                //    }
                //}

                if (!true) {

                } else {
                    /*
                     * REFIX FOR NEXT VERSION, AUTOMATIC CLOSURE ON ACTIVE COLUMN
                     * THIS FUNCTION JUST TEMPORARY MOVE TO ANOTHER CLASS
                     */
                    if (!is_null($this->request->search['value'])) {
                        // dd("W");
                        $searchTerm = $req->search["value"];
                        $availCol = $this->getSearchAbleColumn();

//                        if ($this->query instanceof EloquentBuilder) {
//
//                        } else if ($this->query instanceof QueryBuilder) {
                        $this->query->where(function ($call) use ($searchTerm , $availCol) {
//                            if(!true){
//
//                            } else {
                            foreach ($availCol as $k => $col) {
                                $sourCol = !is_null($col["name"]) ? $col["name"] : $col["data"];

                                if (Str::contains($sourCol , ".")) {
                                    $tmp = explode("." , $sourCol);
//                                    [$relationName, $relationAttribute] = explode('.', $sourCol);

//                                    dd($relationAttribute, $tmp[1]);
                                    if (sizeof($tmp) == 2) {
                                        $call->orWhereHas($tmp[0] , function (EloquentBuilder $w1) use ($tmp , $searchTerm) {
//                                            dd($tmp[1]);
                                            $w1->where($tmp[1] , 'like' , strtolower($searchTerm) . "%");
                                        });
                                    } else if (sizeof($tmp) == 3) {
                                        $call->orWhereHas($tmp[0] , function (EloquentBuilder $w1) use ($tmp , $searchTerm) {
                                            $w1->whereHas($tmp[1] , function (EloquentBuilder $w2) use ($tmp , $searchTerm) {
                                                $w2->where($tmp[2] , 'like' , strtolower($searchTerm) . "%");
                                            });
                                        });
                                    } else {

                                    }
//                                        $call = $this->addWhereHas($call, $sourCol, $searchTerm);
//                                        [$relationName, $relationAttribute] = explode('.', $sourCol);
//                                        dd($call->toSql());
//
//                                        $call->orWhereHas($relationName, function (EloquentBuilder $relation) use ($relationAttribute, $searchTerm) {
//                                            if (strtolower($this->searchMode) == "have") {
//                                                $relation->where($relationAttribute , 'like' , "%" . strtolower($searchTerm) . "%");
//                                            } else if (strtolower($this->searchMode) == "start") {
//                                                $relation->where($relationAttribute , 'like' , strtolower($searchTerm) . "%");
//                                            } else if (strtolower($this->searchMode) == "end") {
//                                                $relation->where($relationAttribute , 'like' , "%" . strtolower($searchTerm));
//                                            }
//                                        });

//                                        dd($relationAttribute);
                                } else {
                                    if (strtolower($this->searchMode) == "have") {
                                        $call->orWhere(DB::raw("LOWER({$sourCol})") , 'like' , "%" . strtolower($searchTerm) . "%");
                                    } else if (strtolower($this->searchMode) == "start") {
                                        $call->orWhere(DB::raw("LOWER({$sourCol})") , 'like' , strtolower($searchTerm) . "%");
                                    } else if (strtolower($this->searchMode) == "end") {
                                        $call->orWhere(DB::raw("LOWER({$sourCol})") , 'like' , "%" . strtolower($searchTerm));
                                    } else if (strtolower($this->searchMode) == "equal") {
                                        $call->orWhere(DB::raw("LOWER({$sourCol})") , '=' , strtolower($searchTerm));
                                    }
                                }
                            }
//                            }
                        });
//                        }
                    }
                }
            }
        } catch (\ErrorException $e) {

        }

        $this->qFilter = clone $this->query;

        if (!$this->getShowFiltered()) {
            $this->q = clone $this->query;
        }

        try {
            if (!is_null($this->request->order)) {
//                dd($this->request->order);
                $this
                    ->q
                    ->orderBy(
                        $this->sortDecorator($this->request->order[0]["column"]) ,
                        $this->request->order[0]["dir"]
                    );
            }
        } catch (\Exception $e) {

        }

        $q = clone $this->q;


        $length = !is_null($this->request->length) ? $this->request->length : 20;
        $page = !is_null($this->request->start) ? $this->request->start : 0;

        if ($length > 0) {
            $q->limit($length)
                ->offset($page);
        }

//        dd($this->getSql($this->q));

        $this->data = $q
            ->get();

        return collect($this->data);
    }

    public function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }

    public function getShowFiltered()
    {
        return $this->showFiltered;
    }

    /*
    * $oaram = bool
    */
    public function setShowFiltered($param)
    {
        $this->showFiltered = $param;
    }

    public function getSearchAbleColumn()
    {
        if (!is_null($this->column)) {
            return array_filter($this->column , function ($item) {
                if (filter_var($item["searchable"] , FILTER_VALIDATE_BOOLEAN)) {
                    return true;
                }
                return false;
            });
        }
    }

    /*
    * $pos = int
    */
    private function sortDecorator($pos = 0)
    {
        if (!is_null($pos)) {
            $_column = $this->column;

            $_columnName = !is_null($_column[$pos]["name"]) ? $_column[$pos]["name"] : $_column[$pos]["data"];
            return $_columnName;
        }

    }

    public function getRowIndex()
    {
        return $this->rowIndex;
    }

    /*
    * $state = bool
    */
    public function setRowIndex($state)
    {
        $this->rowIndex = $state;
    }

    /*
    * $query = string
    */
    private function getTotalDataByQuery($query)
    {
        $_array = explode("limit" , $query);
        $_query = "select count(*) q from (" . $_array[0] . ") x";
        $_data = DB::select($_query);

        return $_data[0]->q;
    }

    private function getSql($model)
    {
        $replace = function ($sql , $bindings) {
            $needle = '?';
            foreach ($bindings as $replace) {
                $pos = strpos($sql , $needle);
                if ($pos !== false) {
                    if (gettype($replace) === "string") {
                        $replace = ' "' . addslashes($replace) . '" ';
                    }
                    $sql = substr_replace($sql , $replace , $pos , strlen($needle));
                }
            }
            return $sql;
        };
        $sql = $replace($model->toSql() , $model->getBindings());

        return $sql;
    }

    public function getSearchMode()
    {
        return $this->searchMode;
    }

    /*
    * $mode = string
    */
    public function setSearchMode($mode)
    {
        $this->searchMode = $mode;
        return $this;
    }

    public function setRowId($args)
    {
        if ($args instanceof \Closure) {
            $this->rowIdClosure = $args;
        } else {
            $this->rowId = $args;
        }

        return $this;
    }

    public function test(...$string)
    {
        return $string;
    }
}
