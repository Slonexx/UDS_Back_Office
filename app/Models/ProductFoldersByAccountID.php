<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFoldersByAccountID extends Model
{
    use HasFactory;

    protected $table = "product_folders_by_account_i_d_s";

    protected $fillable = [
        'accountId',
        'FolderName',
        'FolderID',
        'FolderURLs',
    ];




    public static function getInformation($accountId): object
    {
        $model = ProductFoldersByAccountID::where('accountId',  $accountId )->get();
        if (!$model->isEmpty()) {
            $return = [];
            foreach ($model as $item) {
                $return[] = $item->toArray();
            }
            return (object) [
                'query' => $model,
                'toArray' => $return,
            ];
        } else {
            return (object) [
                'query' => $model,
                'toArray' => null,
            ];
        }
    }
}
