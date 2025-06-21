<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    private function validateData(Array $data) {
        $validation = Validator::make($data, [
            'name' => 'required|unique:categories',
        ]);
        $validationFailed = $validation->fails();
        return [$validationFailed, $validationFailed ? $validation->errors() : null];
    }

    public function Create(Request $request) {
        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $category = new Category();
        $category->name = $request->post('name');
        $category->save();

        return $category;
    }

    public function GetAll(Request $request) {
        return Category::get();
    }

    public function Get(Request $request, int $id) {
        return Category::findOrFail($id);
    }

    public function Modify(Request $request, int $id) {
        $category = Category::findOrFail($id);

        [$validationFailed, $validationErrors] = $this->validateData($request->all());
        if ($validationFailed)
            return response($validationErrors, 401);

        $category->name = $request->post('name');
        $category->save();

        return $category;
    }

    public function Delete(Request $request, int $id) {
        Category::findOrFail($id)->delete();
        return response()->json([
            'deleted' => true
        ]);
    }
}
