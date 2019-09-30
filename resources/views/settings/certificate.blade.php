@extends('layouts.app')

@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">{{__("Ana Sayfa")}}</a></li>
            <li class="breadcrumb-item"><a href="{{route('settings')}}">{{__("Ayarlar")}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{__("Sertifika Ekle")}}</li>
        </ol>
    </nav>
    <h3>{{__("Sisteme SSL Sertifikası Ekleme")}}</h3>
    @if(request('server_id'))
        <h5>{{server()->name . " " . __("sunucusu talebi.")}}</h5>
    @endif
    <table class="notDataTable">
        <tr>
            <td>{{__("Hostname")}}</td>
            <td><input type="text" name="hostname" class="form-control" id="hostname" value="{{request('hostname')}}"></td>
        </tr>
        <tr>
            <td>{{__("Port")}}</td>
            <td><input type="number" name="port" class="form-control" aria-valuemin="1" aria-valuemax="65555" id="port" value="{{request('port')}}"></td>
            <td><button onclick="retrieveCertificate()" class="btn btn-success">{{__("Al")}}</button></td>
        </tr>
    </table>
    <h3>{{__("Sertifika Bilgileri")}}</h3>
    <div class="row">
        <div class="col-md-4">
            <div class="box box-solid">
              <div class="box-header with-border">
                <h3 class="box-title">{{__("İmzalayan")}}</h3>
              </div>
              <div class="box-body clearfix">
                <div class="form-group">
                    <label>{{__("İstemci")}}</label>
                    <input type="text" id="issuerCN" readonly class="form-control">
                </div>
                <div class="form-group">
                    <label>{{__("Otorite")}}</label>
                    <input type="text" id="issuerDN" readonly class="form-control">
                </div>
              </div>
            </div>
        </div>
        <div class="col-md-4">
                <div class="box box-solid">
                  <div class="box-header with-border">
                    <h3 class="box-title">{{__("Parmak İzleri")}}</h3>
                  </div>
                  <div class="box-body clearfix">
                    <div class="form-group">
                        <label>{{__("İstemci")}}</label>
                        <input type="text" id="subjectKeyIdentifier" readonly class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{__("Otorite")}}</label>
                        <input type="text" id="authorityKeyIdentifier" readonly class="form-control">
                    </div>
                  </div>
                </div>
            </div>
        <div class="col-md-4">
            <div class="box box-solid">
              <div class="box-header with-border">
                <h3 class="box-title">{{__("Geçerlilik Tarihi")}}</h3>
              </div>
              <div class="box-body clearfix">
                <div class="form-group">
                    <label>{{__("Başlangıç Tarihi")}}</label>
                    <input type="text" id="validFrom" readonly class="form-control">
                </div>
                <div class="form-group">
                    <label>{{__("Bitiş Tarihi")}}</label>
                    <input type="text" id="validTo" readonly class="form-control">
                </div>
              </div>
            </div>
        </div>
      </div>
      <div class="row">
            <div class="col-md-4">
                <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">{{__("Sertifikayı Onayla")}}</h3>
                </div>
                <div class="box-body clearfix">
                    <span>{{__("Not : Eklediğiniz sertifika işletim sistemi tarafından güvenilecektir.")}}</span><br><br>
                    <button class="btn btn-success" onclick="verifyCertificate()" id="addButton" disabled>{{__("Sertifikayı Onayla")}}</button>
                </div>
            </div>
      </div>
    <script>
        let path = "";
        function retrieveCertificate() {
            Swal.fire({
                position: 'center',
                type: 'info',
                title: '{{__("Sertifika Alınıyor...")}}',
                showConfirmButton: false,
                allowOutsideClick : false,
            });
            let form = new FormData();
            form.append('hostname',$("#hostname").val());
            form.append('port',$("#port").val());
            request('{{route('certificate_request')}}',form,function (success) {
                let json = JSON.parse(success)["message"];
                if(json["issuer"]["DC"]){
                    $("#issuerCN").val(json["issuer"]["CN"]);
                }
                if(json["issuer"]["DC"]){
                    $("#issuerDN").val(json["issuer"]["DC"].reverse().join('.'));
                }
                $("#validFrom").val(json["validFrom_time_t"]);
                $("#validTo").val(json["validTo_time_t"]);
                $("#authorityKeyIdentifier").val(json["authorityKeyIdentifier"]);
                $("#subjectKeyIdentifier").val(json["subjectKeyIdentifier"]);
                $("#addButton").prop('disabled',false);
                path = json["path"];
            },function (errors) {
                let json = JSON.parse(errors);
                Swal.fire({
                    position: 'center',
                    type: 'error',
                    title: json["message"],
                    showConfirmButton: false,
                    allowOutsideClick : false,
                    timer : 2000
                });
            });

        }
        
        function verifyCertificate() {
            Swal.fire({
                position: 'center',
                type: 'info',
                title: '{{__("Sertifika Ekleniyor...")}}',
                showConfirmButton: false,
                allowOutsideClick : false,
            });
            let form = new FormData();
            form.append('path',path);
            form.append('server_hostname',$("#hostname").val());
            form.append('origin',$("#port").val());
            form.append('notification_id','{{request('notification_id')}}');
            form.append('server_id','{{request('server_id')}}');
            request('{{route('verify_certificate')}}',form,function (success) {
                let json = JSON.parse(success);
                Swal.fire({
                    position: 'center',
                    type: 'info',
                    title: json["message"],
                    showConfirmButton: false,
                    allowOutsideClick : false,
                    timer : 2000
                });
                setTimeout(function () {
                    location.href = "{{route('settings')}}" + "#certificates";
                },1000);
            },function (errors) {
                let json = JSON.parse(errors);
                Swal.fire({
                    position: 'center',
                    type: 'error',
                    title: json["message"],
                    showConfirmButton: false,
                    allowOutsideClick : false,
                    timer : 2000
                });
            });
        }
    </script>
@endsection