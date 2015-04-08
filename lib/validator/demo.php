<form action="" method="post" enctype="multipart/form-data">
<input type="file" name="test"/>
<input type="file" name="test1"/>
<input type="submit" />
</form>

<?php

ini_set('display_errors', true);
error_reporting(E_ALL);
set_time_limit(0);

include 'Validator.php';

$post = array(
    'abc'=>123,
    'pass'=>'321',
    'pass_confirmation'=>'321',
    'bcd'=>'yes',
    'date'=>'2015-10-6',
    'bo'=>true
);

$v = Validator::make(array(
    'abc'=>'required|min:5',
    'bcd'=>'required|accepted',
    'date'=>'after:2015-10-5',
    'pass'=>'confirmed',
    'bo'=>'boolean'
),$post);

$messages = array(
    'same'    => 'The :attribute and :other must match.',
    'size'    => 'The :attribute must be exactly :size.',
    'between' => 'The :attribute must be between :min - :max.',
    'in'      => 'The :attribute must be one of the following types: :values',
);
Validator::setMessage($messages);

if ($v->fails()){
        echo 'fails';
        var_dump($v->messages());
}else{
        echo 'OK';
}