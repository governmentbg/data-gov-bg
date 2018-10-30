<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertSystemUserInUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!config('app.IS_TOOL')) {
            $errors = [];

            $password = config('app.SYSTEM_PASSWORD');

            if (!empty($password)) {
                try {
                    DB::table('users')->insert([
                        'username'      => 'system',
                        'password'      => bcrypt($password),
                        'email'         => '',
                        'firstname'     => '',
                        'lastname'      => '',
                        'add_info'      => '',
                        'is_admin'      => 1,
                        'active'        => 1,
                        'approved'      => 1,
                        'api_key'       => Uuid::generate(4)->string,
                        'hash_id'       => '',
                        'created_at'    => gmdate('Y-m-d H:i:s'),
                        'created_by'    => 1,
                    ]);
                } catch (\Illuminate\Database\QueryException $ex) {
                    $errors[] = $ex->getMessage();
                }
            } else {
                $errors[] = 'SYSTEM_PASSWORD not entered in config';
            }

            if (!empty($errors)) {
                dd($errors[0]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!config('app.IS_TOOL')) {
            try {
                DB::table('users')->where('username', 'system')->delete();
            } catch (\Illuminate\Database\QueryException $ex) {}
        }
    }
}
