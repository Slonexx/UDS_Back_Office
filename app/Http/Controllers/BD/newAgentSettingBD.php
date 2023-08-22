<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\newAgentModel;
use App\Models\newProductModel;
use App\Models\SettingMain;
use GuzzleHttp\Exception\BadResponseException;

class newAgentSettingBD extends Controller
{
    public mixed $accountId;
    public mixed $unloading;
    public mixed $examination;
    public mixed $email;
    public mixed $gender;
    public mixed $birthDate;
    public mixed $url;
    public mixed $offset;

    public function __construct($accountId)
    {
        $this->accountId = $accountId;

        $find = newAgentModel::query()->where('accountId', $accountId)->first();
        try {

            if ($find != []){
                $result = $find->getAttributes();

                $this->unloading = $result['unloading'];
                $this->examination = $result['examination'];
                $this->email = $result['email'];
                $this->gender = $result['gender'];
                $this->birthDate = $result['birthDate'];

                $this->url = $result['url'];
                $this->offset = $result['offset'];
            } else {
                $this->unloading = '0';
                $this->examination = null;
                $this->email = null;
                $this->gender = null;
                $this->birthDate = null;

                $this->url = null;
                $this->offset = null;
            }
        } catch (BadResponseException $e) {
            $this->unloading = '0';
            $this->examination = null;
            $this->email = null;
            $this->gender = null;
            $this->birthDate = null;

            $this->url = null;
            $this->offset = null;
        }
    }

}
