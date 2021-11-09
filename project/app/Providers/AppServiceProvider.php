<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use App\Model\User;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('path.public', function () {
          return base_path() . '/../public';
      });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
      if(env('FORCE_HTTPS')) {
          URL::forceScheme('https');
      }

      setlocale(LC_ALL, 'id_ID.utf8');

      DB::enableQueryLog();
      // DB::table('users')->enableQueryLog();
      // DB::table('roles')->enableQueryLog();
      //

      DB::listen(function($query)  {
        $token = \Request::bearerToken() ?? "null";
        $date=new \DateTime();
        $ip=\Request::ip();
        $timestamp=$date->format("Y-m-d H:i:s.v");
        $today=date("Y-m-d");

        if (trim($query->sql)[0]!=="s") {
          $filename="/logs/xquery.".$today.".log";
          // $content=vsprintf(str_replace(array('?'), array('\'%s\''), $query->sql), $query->bindings)."; // {$timestamp} {$auth_type} {$auth_id} {$ip}". PHP_EOL;
        }else {
          $filename="/logs/select.".$today.".log";
          // if (auth()->guard("web")->check()) {
          //   $auth_type = "User";
          //   $auth_id = auth()->guard("web")->id();
          // }elseif (auth()->guard("admin")->check()) {
          //   $auth_type = "Admin";
          //   $auth_id = auth()->guard("admin")->id();
          // }else {
          //   $auth_type = "None";
          //   $auth_id = 0;
          // }

        }
        $content=vsprintf(str_replace(array('?'), array('\'%s\''), $query->sql), $query->bindings)."; // {$timestamp} {$ip} // ; {$token}". PHP_EOL;
        \File::append(storage_path($filename),$content);

      });
    }
}
