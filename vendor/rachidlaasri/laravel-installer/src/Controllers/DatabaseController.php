<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;
use Request;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {

        $purchase_code = env('PURCHASE_CODE', 'default_value');
 	    $app_key = env('APP_KEY', 'default_value');
        $version = env('APP_VERSION', 'default_value');
        $resp_data = [];
        $errorMessage = "Something went wrong!";
        $server_name = Request::server("SERVER_NAME");

        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->get("https://verification.goapps.co.in/verify/" . $purchase_code . "/" . $server_name . "/" . $version. "/" . $app_key);
            $resp_code = $res->getStatusCode();
            $resp_data = json_decode($res->getBody(), true);
        } catch (\Throwable$th) {
            $resp_code = 0;
            $resp_data = [];
        }

        Artisan::call('migrate:reset', ['--force' => true]);

        if ($resp_data) {
            if ($resp_data['status'] == true) {
                $config_data = $resp_data['data'];

                $response = $this->databaseManager->migrateAndSeed();

                for ($i = 0; $i < count($config_data); $i++) {
                    DB::table('config')->insert([
                        'config_key' => $config_data[$i]['config_key'],
                        'config_value' => $config_data[$i]['config_value'],
                    ]);
                }

                return redirect()->route('LaravelInstaller::final')->with(['message' => $response]);

            } else {
                $errorMessage = $resp_data['message'];
                return redirect()->route('LaravelInstaller::environmentClassic')->with([
                    'message' => $errorMessage,
                ]);
            }
        } else {
            return redirect()->route('LaravelInstaller::environmentClassic')->with([
                'message' => $errorMessage,
            ]);
        }

    }
}
