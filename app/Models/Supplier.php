<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model {
	protected $fillable = ['nama', 'alamat', 'email', 'telepon'];

	protected $hidden = ['created_at', 'updated_at'];
}
