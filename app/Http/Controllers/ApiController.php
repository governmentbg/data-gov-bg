<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\QueryException;
use PDOException;
use App\User;
use App\Role;
use App\RoleRight;

class ApiController extends Controller
{
    const ERROR_GENERAL = 'General';
    const ERROR_ACCESS = 'Access denied';
}
