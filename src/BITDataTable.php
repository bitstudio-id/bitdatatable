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
    private $request;

    /*
     * $q = QUERY
     * */
    private $q;
    private $query;
    private $qFilter;

    private $queryMode = null;
    private $rowIndex;

    private $column;
    private $mapColumn;
    private $closure;
    private $showTotal;
    private $showFiltered;

    private $rowIndexName;
    private $searchMode;

    public function __construct()
    {
        $this->mapColumn = [];
        $this->data = [];
        $this->showTotal = true;
        $this->showFiltered = false;
        $this->rowIndex = false;
        $this->rowIndexName = "DT_Row_Index";
        $this->searchMode = "start";
    }

    public function getRowIndexName(): string
    {
        return $this->rowIndexName;
    }

    /*
     * DEFAULT CONSTRUCTOR IS EMPTY
     * */

    public function setRowIndexName(string $name)
    {
        $this->rowIndexName = $name;
    }

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

    public function from($query)
    {
        if ($query instanceof QueryBuilder) {
            $this->query = $query;
            $this->queryMode = "query_builder";
        } else if ($query instanceof EloquentBuilder) {
            $this->query = $query;
            $this->queryMode = "eloquent";
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

        $data = is_null($this->closure) ? $this->getData() : $this->getData()->map($this->closure);

        if ($this->getRowIndex()) {
            $i = 1;
            foreach ($data as $key => $item) {
                $data[$key]->{$this->rowIndexName} = (($this->request->start / $this->request->length) * $this->request->length) + $i;
                $i++;
            }
        }

        $recordsTotal = $this->getTotalDataByQuery($this->getSql($this->q));
        $recordsFiltered = $this->getShowFiltered() ? $this->getTotalDataByQuery($this->getSql($this->qFilter)) : $recordsTotal;


        return [
            "data" => $data ,
            "draw" => $this->request->draw ,
            "recordsTotal" => $recordsTotal ,
            "recordsFiltered" => $recordsFiltered ,
        ];
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
                                            $w1->where($tmp[1] , 'like' , "%" . strtolower($searchTerm) . "%");
                                        });
                                    } else if (sizeof($tmp) == 3) {
                                        $call->orWhereHas($tmp[0] , function (EloquentBuilder $w1) use ($tmp , $searchTerm) {
                                            $w1->whereHas($tmp[1] , function (EloquentBuilder $w2) use ($tmp , $searchTerm) {
                                                $w2->where($tmp[2] , 'like' , "%" . strtolower($searchTerm) . "%");
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
                $this
                    ->query
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

    public
    function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }

//    public function eloquent(\Illuminate\Database\Eloquent\Builder $query)
//    {
//        $this->query = $query;
//        return $this;
//    }

    public
    function getShowFiltered(): bool
    {
        return $this->showFiltered;
    }

    public
    function setShowFiltered(bool $param): void
    {
        $this->showFiltered = $param;
    }

    public
    function getSearchAbleColumn()
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

    private
    function sortDecorator(int $pos = 0)
    {
        if (!is_null($pos)) {
            $_column = $this->column;

            $_columnName = !is_null($_column[$pos]["name"]) ? $_column[$pos]["name"] : $_column[$pos]["data"];
            return $_columnName;
        }

    }

    public
    function getRowIndex(): bool
    {
        return $this->rowIndex;
    }

    public
    function setRowIndex(bool $state): void
    {
        $this->rowIndex = $state;
    }

    private
    function getTotalDataByQuery(string $query): int
    {
        $_array = explode("limit" , $query);
        $_query = "select count(*) q from (" . $_array[0] . ") x";
        $_data = DB::select($_query);

        return $_data[0]->q;
    }

    private
    function getSql($model)
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

    public
    function getSearchMode(): string
    {
        return $this->searchMode;
    }

    public
    function setSearchMode(string $mode)
    {
        $this->searchMode = $mode;
        return $this;

    }

    private
    function addWhereHas(EloquentBuilder $builder , string $relation , string $searchTerm)
    {
//        dd(explode())
//        $tmpRelation = explode(".", $relation);
//        $tmp = $relation;
//        foreach ($tmpRelation as $k => $v) {
//            if($k == (sizeof($tmpRelation) - 1)){
//                $builder->where($v , 'like' , "%" . strtolower($searchTerm) . "%");
//            } else {
//                $tmp = str_replace($v.".", "", $tmp);
//                $builder->orWhereHas($v, function (EloquentBuilder $relation) use ($tmp, $searchTerm) {
//                    $this->addWhereHas($relation, $tmp, $searchTerm);
//                });
//            }
//        }

//        dd($builder->toSql());
        return $builder;
    }
}
