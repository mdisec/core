<?php

namespace App\Http\Controllers\Server;

use App\AdminNotification;
use App\Certificate;
use App\Classes\Connector\WinRMConnector;
use App\Http\Controllers\Controller;
use App\Key;
use App\Permission;
use App\Script;
use App\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Notification;

class AddController extends Controller
{
    /**
     * @var \App\Server
     */
    public $server;

    public function main()
    {
        $this->authorize('create','\App\Server');

        // Check if name is already in use.
        if(Server::where([
            'user_id' => auth()->id(),
            "name" => request('name')
        ])->exists()){
            return respond("Bu sunucu ismiyle bir sunucu zaten var.",201);
        }

        // Create object with parameters.
        $this->server = new Server();
        $this->server->fill(request()->all());
        if(request('username') && request('password')){
            $this->server->type = (($this->server->type == "windows") ? "windows_powershell" : "linux_ssh");
        }

        $this->server->user_id = auth()->id();

        // Check if Server is online or not.
        if(!$this->server->isAlive()){
            return respond("Sunucuyla bağlantı kurulamadı.",406);
        }

        $this->server->save();
        Notification::new(
            __("Yeni sunucu eklendi."),
            "notify",
            __(":server (:ip) isimli yeni bir sunucu eklendi.", ["server" => $this->server->name, "ip" => $this->server->ip_address])
        );
        // Add Server to request object to use it later.
        request()->request->add(["server" => $this->server]);

        // Run required function for specific type.
        $next = null;
        switch ($this->server->type){
            case("linux"):
                $next = $this->linux();
                break;

            case("linux_ssh"):
                $next = $this->linux_ssh();
                break;

            case("windows");
                $next = $this->windows();
                break;

            case("windows_powershell"):
                $next = $this->windows_powershell();
                break;

            default:
                $next = respond("Sunucu türü bulunamadı.",404);
                break;
        }
        return $next;
    }

    private function linux_ssh()
    {
        $key = new Key(request()->all());

        $key->server_id = $this->server->id;
        $key->user_id = auth()->user()->id;
        $key->save();

        // Create Key
        $flag = \App\Classes\Connector\SSHConnector::create($this->server,request('username'), request('password'),auth()->id(),$key);
        if(!$flag){
            $this->server->delete();
            $key->delete();
            return respond("SSH Hatası",400);
        }

        foreach (extensions() as $extension){
            $script = Script::where('unique_code',strtolower($extension->name) . "_discover")->first();
            if($script){
                try{
                    $output = $this->server->runScript($script,"");
                    if($output == "YES\n"){
                        DB::table('server_extensions')->insert([
                            "id" => Str::uuid(),
                            "server_id" => $this->server()->id,
                            "extension_id" => $extension->id
                        ]);
                    }
                }catch (\Exception $exception){};
            }
        }

        return $this->grantPermissions();
    }

    private function linux(){
        return $this->grantPermissions();
    }

    private function windows(){
        return $this->grantPermissions();
    }

    private function windows_powershell(){
        $key = new Key(request()->all());

        $key->server_id = $this->server->id;
        $key->user_id = auth()->user()->id;
        $key->save();
        $flag = WinRMConnector::create($this->server,request('username'), request('password'),auth()->id(),$key);

        if(!$flag){
            $this->server->delete();
            $key->delete();
            return respond("WinRM Hatası",400);
        }

        return $this->grantPermissions();
    }

    private function grantPermissions()
    {
        Permission::grant(user()->id,'server','id',$this->server->id);

        // SSL Control
        $possiblePorts = ["636","5986"];
        if(in_array($this->server->control_port, $possiblePorts)){
            $cert = Certificate::where([
                'server_hostname' => $this->server->ip_address,
                'origin' => $this->server->control_port
            ])->first();
            if(!$cert){
                // Notify Admins
                $this->server->enabled = false;
                $this->server->save();
                $notification = new AdminNotification();
                $notification->title = "Yeni Sertifika Onayı";
                $notification->type = "cert_request";
                $notification->message = $this->server->ip_address . ":" . $this->server->control_port . ":" . $this->server->id;
                $notification->level = 3;
                $notification->save();
                return respond("Bu sunucu ilk defa eklendiğinden dolayı bağlantı sertifikası yönetici onayına sunulmuştur. Bu sürede sunucuya erişemezsiniz.",202);
            }
        }
        return respond(route('server_one',$this->server->id),300);
    }
}
