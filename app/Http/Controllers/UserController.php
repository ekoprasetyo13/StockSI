<?php

namespace App\Http\Controllers;

use App\Exports\ExportSuppliers;
use App\Imports\SuppliersImport;
use App\Models\Supplier;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller {
	public function __construct() {
		$this->middleware('role:admin,staff');
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		// $users = User::all();
		return view('user.index');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$this->validate($request, [
			'name' => 'required',
			'email' => 'required|unique:suppliers',
		]);

		User::create($request->all());

		return response()->json([
			'success' => true,
			'message' => 'Suppliers Created',
		]);

	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		$users = User::find($id);
		return $users;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$this->validate($request, [
			'name' => 'required|string|min:2',
			'email' => 'required|string|email|max:255|unique:suppliers',
		]);

		$users = User::findOrFail($id);

		$users->update($request->all());

		return response()->json([
			'success' => true,
			'message' => 'users Updated',
		]);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		User::destroy($id);

		return response()->json([
			'success' => true,
			'message' => 'User Delete',
		]);
	}

	public function apiUsers() {
		$users = User::all();

		return DataTables::of($users)
			->addColumn('action', function ($users) {
				return '<a href="#" class="btn btn-info btn-xs"><i class="glyphicon glyphicon-eye-open"></i> Show</a> ' .
				'<a onclick="editForm(' . $users->id . ')" class="btn btn-primary btn-xs"><i class="glyphicon glyphicon-edit"></i> Edit</a> ' .
				'<a onclick="deleteData(' . $users->id . ')" class="btn btn-danger btn-xs"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
			})
			->rawColumns(['action'])->make(true);
	}

	public function ImportExcel(Request $request) {
		//Validasi
		$this->validate($request, [
			'file' => 'required|mimes:xls,xlsx',
		]);

		if ($request->hasFile('file')) {
			//UPLOAD FILE
			$file = $request->file('file'); //GET FILE
			Excel::import(new SuppliersImport, $file); //IMPORT FILE
			return redirect()->back()->with(['success' => 'Upload file data suppliers !']);
		}

		return redirect()->back()->with(['error' => 'Please choose file before!']);
	}

	public function exportSuppliersAll() {
		$suppliers = Supplier::all();
		$pdf = Pdf::loadView('suppliers.SuppliersAllPDF', compact('suppliers'));
		return $pdf->download('suppliers.pdf');
	}

	public function exportExcel() {
		return (new ExportSuppliers)->download('suppliers.xlsx');
	}
}
