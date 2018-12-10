@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h2>Kullanıcılar</h2>
    </div>
    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#userAdd">
        Kullanıcı Ekle
    </button><br><br>
    <table id="mainTable" class="table">
        <thead>
        <tr>
            <th scope="col">Kullanıcı Adı</th>
            <th scope="col">Email</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody data-toggle="modal" data-target="#new">
        @foreach ($users as $user)
            <tr class="highlight">
                <td>{{$user->name}}</td>
                <td>{{$user->email}}</td>
            </tr>

        @endforeach
        </tbody>
    </table>
    @include('modal',[
                  "id"=>"userAdd",
                  "title" => __("Kullanıcı Ekle"),
                  "url" => "/user/add",
                  "inputs" => [
                      __("Kullanıcı Adı") => "kullanici_adi:text",
                      __("Email Adresi") => "email:text",
                      __("Parola") => "password:password"
                  ],
                  "submit_text" => "Ekle"
              ])
    <div class="modal fade" id="new" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="exampleModalLabel">Kullanıcı İşlemleri</h1>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form onsubmit="add(event,'update')">
                    <div class="modal-body">
                        <td>
                            <div class="form-group">
                                <h3>Kullanıcı Adı</h3>
                                <input id="change_name" type="text" name="kullanici_adi" class="form-control" placeholder="Kullanıcı adı" value="" required minlength="3">
                            </div>
                            <div class="form-group">
                                <h3>Email Adresi</h3>
                                <input id="change_email" type="text" name="email" class="form-control" placeholder="Ex: example@example.com"  value=""required pattern="[a-zA-Z0-9_\.\+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-\.]+">
                            </div>
                            <div class="form-group">
                                <h3>Parola</h3>
                                <input id="change_pass"  placeholder="Parola"  name="password" type="password" value="" class="form-control" required>
                            </div>
                        </td>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" id="button1" data-dismiss="modal" data-toggle="modal" data-target="#check" onclick="deletion(this)">Hesabı Kapat</button>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Çıkış</button>
                        <button  class="btn btn-primary" type="submit" value="Düzenle">Düzenle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="check" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Kullanıcı Sil</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>
                <div class="modal-body">
                    <h3>Kullanıcıyı silmek istediğinize emin misiniz? Bu işlem geri alınamayacaktır.</h3>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">İptal</button>
                    <button type="button" class="btn btn-danger" onclick="deletion(this)">Kullanıcıyı Sil</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function deletion(event){

            $('tr.selected').removeClass('selected');
            // add selected class to current row
            $(this).closest('tr').addClass('selected');
            $.post("" ,{

            },function (data,status) {
                if(data["result"] === 200){
                    location.reload();
                }else{
                    alert("Hata!");
                }
            });
        }
        function add(event) {
            event.preventDefault();
            var x = $("#form").serializeArray();
            dataObj = {};
            $.each(x, function(i, field){

                dataObj[field.name] = field.value;
            });
            $("#add_dhcp").prop("checked") ? features = features + "1" : 0;
            $("#add_dns").prop("checked") ? features = features + "2" : 0;
            $("#add_ldap").prop("checked") ? features = features + "3" : 0;
            $.post("event=='add' ? {{route('server_add')}}:{{route('server_add')}}" ,{
                name : dataObj['name'],
                email : dataObj['email'],
                parola : dataObj['password']
            },function (data,status) {
                if(data["result"] === 200){
                    location.reload();
                    //window.location.replace("{{route('users')}}" + "/" + data["id"]);
                }else{
                    alert("Hata!");
                }
            });
        }
    </script>
@endsection