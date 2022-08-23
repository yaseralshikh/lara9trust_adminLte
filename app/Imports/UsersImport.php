<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel , WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new User([
            'name'     => $row['name'],
            'username' => $row['username'],
            'email'    => $row['email'],
            'phone'   => $row['phone'],
            'description'   => $row['description'],
            'address'   => $row['address'],
            //'password' => Hash::make($row['password']),
        ]);
    }
}
