<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'confirmed' => 'Mật khẩu xác nhận không trùng khớp.',
    'email' => 'Địa chỉ email không hợp lệ.',
    'min' => [
        'numeric' => 'Trường :attribute phải lớn hơn hoặc bằng :min.',
        'file' => 'Dung lượng tập tin :attribute phải lớn hơn hoặc bằng :min kilobytes.',
        'string' => 'Trường :attribute phải có ít nhất :min ký tự.',
        'array' => 'Trường :attribute phải có ít nhất :min phần tử.',
    ],
    'required' => 'Vui lòng nhập :attribute.',
    'unique' => ':Attribute đã có người sử dụng.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    */

    'attributes' => [
        'email' => 'địa chỉ email',
        'password' => 'mật khẩu',
        'name' => 'tên',
    ],

];