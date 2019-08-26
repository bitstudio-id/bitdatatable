# BITDataTable : jQuery DataTables for Laravel 5

This package is created to handle server-side works of DataTables jQuery Plugin via AJAX option by using Eloquent ORM / Query Builder.

This package only tested on Laravel 5.6, 5.7 & 5.8 so if you have any problem or any question you can call me or open new issues

## Quick Installation
```
composer require bitstudio-id/bitdatatable
```

## Requirements
- [PHP >= 5.6.4](http://php.net/)
- [Laravel >= 5.4](https://github.com/laravel/framework)
- [jQuery DataTables v1.10.x](http://datatables.net/)


## Javascript and CSS
```html
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css"/>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"/>

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
```

## Blade view
```html
<table id="table" class="table table-bordered table-striped">
  <thead>
  <tr>
      <th>#ID</th>
      <th>Name</th>
      <th>Role</th>
      <th>Email</th>
      <th>...</th>
  </tr>
  </thead>
  <tbody></tbody>
  </table>
```

## Javascript
```
<script>
  $(document).ready(function () {
    //hide error/warning on datatable
    $.fn.dataTable.ext.errMode = 'none';
    
    var table = $('#table').DataTable({
      //enable filter
      
      bFilter: true,
      processing: true,
      serverSide: true,
      ajax: {
        url: "/dummy/dtb-v2/get",
        type: 'get',
      },
      columns: [
        {data: "id", name: "id", searchable: false, orderable: false},
        {data: "employee.code"},
        {data: "name", name: "user_name"},
        {data: "employee.role.name"},
        {data: "email"},
        {data: "action", searchable: false, orderable: false}, // use searchable: false, orderable: false for custom column
      ],
    });
  });
</script>
```

## How to use with eloquent
```php
use BITStudio\BITDataTable\BITDataTable;
...
...
public function dtbGetV2(Request $request)
{
  $dtb = new BITDataTable();

  // Set request
  $dtb->setRequest($request);

  $user = User::query()->with('employee', 'employee.role');

  $dtb->from($user);
  
  $state = "admin";

  $dtb->addCol(function ($user){
    $user->action = "<a target='_blank' href='//lorem.com/{$user->id}' class='btn btn-danger'>action-{$item->id}</a>";
    return $user;
  });

  return $dtb->generate();
}
```

## How to use logic on addCol
```
$state = "admin";

$dtb->addCol(function ($user) use ($state){
  //use logic on addCol
      
  //set as empty default
  $user->admin_col = "";
  
  if($state == $user->role->name) {
      $user->admin_col .= "admin-col";
  }
   return $user;
});
```
        
## How to use with Query Builder
```php 
use BITStudio\BITDataTable\BITDataTable;
...
...
public function dtbGetV2(Request $request)
  $dtb = new BITDataTable();
  
  $dtb->setRequest($request);
  
  $q = DB::table("orders as o");
  $q->select("o.*", "o.no_cs as customer_number", "e.employee_name as emp_name");
  $q->leftJoin("employee as e", "e.id", "=", "o.employee_id");
  
  $dtb->from($q);
  
  
  //add custom column
  $dtb->addCol(function ($user){
    $user->action = "<a target='_blank' href='//google.com/{$item->id}' class='btn btn-danger'>action-{$item->id}</a>";
  
    return $user;
  });
  
  return $dtb->generate();
}
```

### How to show index number for numbering on view
```
$dtb->setRowIndex(true);
```
this will append property DT_RowIndex on json response
```
columns: [
        {data: "DT_RowIndex", name: "id" searchable: false, orderable: false},
        ...
        ]
```
dont forget to set searchable = false

#### add value class attribute
```
$dtb->addClass("text-danger"); //insert before genereate
```

#### add value id attribute
```
//create custom from collection property
$dtb->setRowId("id");

//create custom from addCol or setRowId for custom id attribute
$dtb->setRowId(function($item) {
    $item->DT_RowId = "id-".$item->id;
    return $item;
});
```
## License
The MIT License (MIT). Please see License File for more information.
