<?php
/**
 * Created by PhpStorm.
 * User: Cacing
 * Date: 12/07/2019
 * Time: 15:00
 */

namespace BITStudio\BITDataTable;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    private $rowIndex;

    private $column;
    private $mapColumn;
    private $closure;
    private $showTotal;
    private $showFiltered;

    private $rowIndexName;


    public function setRowIndexName(string $name) {
        $this->rowIndexName = $name;
    }

    public function getRowIndexName() :string {
        return $this->rowIndexName;
    }

    /*
     * DEFAULT CONSTRUCTOR IS EMPTY
     * */
    public function __construct()
    {
        $this->mapColumn = [];
        $this->data = [];
        $this->showTotal = true;
        $this->showFiltered = false;
        $this->rowIndex = false;
        $this->rowIndexName = "DT_Row_Index";
    }

    public function setShowFiltered(bool $param) : void
    {
        $this->showFiltered = $param;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->column = $this->request->columns;
        return $this;
    }

    public function setData($data = [])
    {
        $this->data = $data;
        return $this;
    }

    public function mapColumn(array $data = []) {

        $this->mapColumn = $data;
        return $this;
    }

    private function sortDecorator(int $pos = 0) {
        if(!is_null($pos)) {
            $_column = $this->column;

            $_columnName = !is_null($_column[$pos]["name"]) ? $_column[$pos]["name"] : $_column[$pos]["data"];
            return $_columnName;
        }

    }

    public function getData(){
        if($this->getShowFiltered()) {
            $this->q = clone $this->query;
        }

        try {
            if(sizeof($this->getSearchAbleColumn()) > 0) {
                foreach ($this->getSearchAbleColumn() as $key => $col) {
                    /*
                     * MAPPING DARI var : $mapColumn
                     * */
                    if(!true) {

                    } else {
                        $sourCol = !is_null($col["name"]) ? $col["name"] : $col["data"];
                        if(!is_null($this->request->search['value'])) {
                            $this->query->orWhere($sourCol, 'like', $this->request->search['value']."%");
                        }
                    }
                }
            }
        } catch (\ErrorException $e) {

        }

        $this->qFilter = clone $this->query;

        if(!$this->getShowFiltered()) {
            $this->q = clone $this->query;
        }

        try {
            if(!is_null($this->request->order)){
                $this
                    ->query
                    ->orderBy(
                        $this->sortDecorator($this->request->order[0]["column"]),
                        $this->request->order[0]["dir"]
                    );
            }
        } catch (\Exception $e) {

        }

        $q = clone $this->q;


        $length = !is_null($this->request->length) ? $this->request->length : 20;
        $page = !is_null($this->request->start) ? $this->request->start : 0;

        if($length > 0) {
            $q->limit($length)
                ->offset($page);
        }

        $this->data = $q
            ->get();

        return collect($this->data);
    }

    public function addCol(\Closure $callback) {
        $this->closure = $callback;
        return $this;
    }

    public function query(Builder $query)
    {
        $this->query = $query;
        return $this;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getSearchAbleColumn()
    {
        if(!is_null($this->column)) {
            return array_filter($this->column, function ($item) {
                if (filter_var($item["searchable"], FILTER_VALIDATE_BOOLEAN)) {
                    return true;
                }
                return false;
            });
        }
    }

    public function getOrderAbleColumn()
    {
        return array_filter($this->column, function ($item) {
            if (filter_var($item["orderable"], FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
            return false;
        });
    }

    public function getShowFiltered() : bool
    {
        return $this->showFiltered;
    }

    public function setRowIndex(bool $state) : void
    {
        $this->rowIndex = $state;
    }

    public function getRowIndex() : bool
    {
        return $this->rowIndex;
    }

    public function generate()
    {

        $data = is_null($this->closure) ? $this->getData() : $this->getData()->map($this->closure);

        if($this->getRowIndex()){
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
            "draw" => $this->request->draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
        ];
    }

    private function getTotalDataByQuery(string $query) : int {

        $_array = explode("limit", $query);
        $_query = "select count(*) q from (".$_array[0].") x";
        $_data = DB::select($_query);

        return $_data[0]->q;
    }

    private function getSql($model)
    {
        $replace = function ($sql, $bindings)
        {
            $needle = '?';
            foreach ($bindings as $replace){
                $pos = strpos($sql, $needle);
                if ($pos !== false) {
                    if (gettype($replace) === "string") {
                        $replace = ' "'.addslashes($replace).'" ';
                    }
                    $sql = substr_replace($sql, $replace, $pos, strlen($needle));
                }
            }
            return $sql;
        };
        $sql = $replace($model->toSql(), $model->getBindings());

        return $sql;
    }
}
