<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminPermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminatech\DataProvider\DataProvider;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->authorize(AdminPermissionEnum::ADMINS()->ability());
    }

    public function index(Request $request)
    {
        $models = (new DataProvider(Admin::class))
            ->sort([
                'id',
                'name',
                'email',
                'role',
                'created_at'
            ])
            ->paginate($request);

        return view('admin.admins.index', [
            'models' => $models,
        ]);
    }
}
