<?php

namespace App\Console\Commands\custom;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Web\RewardController;
use App\Models\newProductModel;
use App\Models\SettingMain;
use App\Services\newProductService\createProductForMS;
use App\Services\newProductService\createProductForUDS;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CronCommandCustomAgent extends Command
{
    protected $signature = 'cronCommand:customAgent';

    protected $description = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): void
    {

        $model = SettingMain::find('239d84db-0939-11eb-0a80-03af0000352d');
        //$model = SettingMain::find('1dd5bd55-d141-11ec-0a80-055600047495');
        if ($model != null) $model = $model->toArray();

        $client_ms = new MsClient($model['TokenMoySklad']);
        $client_uds = new UdsClient($model['companyId'], $model['TokenUDS']);

        $is_next = true;
        $offset = 0;
        $requestCount = 0;
        $bonus = 0;

        $customers = $client_uds->newGET('https://api.uds.app/partner/v2/customers/find?phone=%2b'.'77777777777');
        if ($customers->status) $bonus = $customers->data->user->participant->membershipTier->rate;
        //dd($list_counterparty);
        do {
            $list_counterparty = $client_ms->newGet('https://api.moysklad.ru/api/remap/1.2/entity/counterparty?limit=1000&offset=' . $offset);

            if (!$list_counterparty->status)break;


            if ($list_counterparty->data->rows != [])
                foreach ($list_counterparty->data->rows as $i=>$item) {
                    $requestCount++;

                    if ($requestCount % 30 == 0) sleep(2);
                    if (property_exists($item, 'phone')) {
                        $this->info($i.') '.$item->name.' проверка, номер есть в МС');
                        $phone = $this->getPhone($item);
                        $total = $this->getTotal($item);
                        $bonus_stack = $this->getBonus($item);

                        $e164PhoneNumber = str_replace('+', '', $phone); // Удаляем символ "+"
                        $url = 'https://api.uds.app/partner/v2/customers/find?phone=%2b'.$e164PhoneNumber;
                        $body = $client_uds->newGET($url);
                        if ($body->status) {
                            if ($body->data->user->phone == null) {
                                $skipLoyaltyTotal = $this->newOperationsBonus($total, $bonus, $bonus_stack);
                                $json = [
                                    'code' => null,
                                    'participant' => [
                                        'uid' => null,
                                        'phone' => $phone,
                                    ],
                                    'nonce' => null,
                                    'cashier' => null,
                                    'receipt' => [
                                        'total' => (float)$total,
                                        'cash' => (float)$total,
                                        'points' => (float)0.0,
                                        'number' => null,
                                        'skipLoyaltyTotal' => (float)$skipLoyaltyTotal,
                                    ],
                                    'tags' => null,
                                ];

                                $req = $client_uds->newPOST('https://api.uds.app/partner/v2/operations', $json);
                                if ($req->status) {
                                    $client_ms->newPUT($item->meta->href, ['externalCode' =>'' . $req->data->customer->id]);
                                    $this->info('~~~УСПЕШНО~~~ отправлен, номер телефона ' . $phone);
                                } else continue;
                            }
                            else $this->info('ИНФОРМАЦИЯ, в uds уже загружен, номер '.$phone);
                        } else continue;

                    } else continue;
                }
            else $is_next = false;


            $offset = $offset + 1000;
        } while ($is_next);


    }

    private function getPhone(mixed $item): string
    {
        $phone = $item->phone;
        $phone_cleaned = preg_replace('/[\s\(\)\-]/', '', $phone);
        $phone_cleaned = preg_replace(' ', '', $phone_cleaned);
        $last_ten = substr($phone_cleaned, -10);
        return '+7' . $last_ten;
    }

    private function getTotal(mixed $item): float
    {
        if (property_exists($item, 'salesAmount')) {
            $sum = $item->salesAmount;
            if ($sum > 0) return round($sum / 100, 2);
            else  return 1.0;
        } else return 1.0;
    }

    private function getBonus(mixed $item): float
    {
        if (property_exists($item, 'bonusPoints')) {
            if ($item->bonusPoints > 0) return round($item->bonusPoints, 2);
            else  return 0;
        }
        else return 0;
    }

    private function newOperationsBonus(float $total, int $bonus, float $bonus_stack): float
    {
        if ($bonus_stack != 0 and $total > $bonus_stack and $bonus > 0) {
            $x1 = $bonus_stack * 100;
            $x2 = $x1 / $bonus;
            return round($total - $x2, 2);
        }
        return $total;
    }
}
