<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'value_idr',
       'transaction_id',
       'transaction_category_id',
       'group'
    ];

    public function transaction (){
       return $this->belongsTo(TransactionHeader::class, 'transaction_id', 'id');
    }
     public function category (){
       return $this->belongsTo(MsCategory::class, 'transaction_category_id', 'id');
    }
}
