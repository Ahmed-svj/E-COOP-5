<?php
namespace Controllers\Admin;

use Auth;
use Controller;

abstract class Base extends Controller
{
    protected function adminId(): int
    {
        return (int) Auth::profile()['AdminID'];
    }
}
