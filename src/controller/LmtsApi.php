<?php

namespace Lmts\src\controller;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;

class LmtsApi extends Controller
{
    private $api;
    private $modulo;
    private $client;

    public function __construct()
    {
      $this->api = config('lmts.url');
      $this->modulo = config('lmts.modulo');
      $this->client = new Client();
    }

    public function check(){

      $response = $this->client->request('GET',$this->api . 'check/', [
        'headers' => [
          'Authorization' => session('token_type').' '.session('access_token'),
          'Content-Type' => 'application/json',
          'X-Requested-With' => 'XMLHttpRequest'
        ]
      ]);
      if($response->getStatusCode() == 201){
        return true;
      }
      else{
        return false;
      }
    }

    public function getCursos(){
      $response = $this->client->request('GET',$this->api . $this->modulo .'/getUnidades/6/5', [
        'headers' => [
          'Authorization' => session('token_type').' '.session('access_token'),
          'Content-Type' => 'application/json',
          'X-Requested-With' => 'XMLHttpRequest'
        ]
      ]);
      if($response->getStatusCode() == 200){
        $response = json_decode($response->getBody(), true);
        $aux = [];
        // dd($response);
        for($i = 0; $i < sizeof($response); $i++){
          $nomeCampus = '';
          $nomeDep = '';
          $idCurso = '';
          $nomeCurso = '';
          for($k = 0; $k < sizeof($response[$i]); $k++){
            if($response[$i][$k]['tipoUnidade'] == 'Campus'){
              $nomeCampus = $response[$i][$k]['nome'];
            }
            if($response[$i][$k]['tipoUnidade'] == 'Departamento'){
              $nomeDep = $response[$i][$k]['nome'];
            }
            if($response[$i][$k]['tipoUnidade'] == 'Curso de Graduação'){
              $nomeCurso = $response[$i][$k]['nome'];
              $idCurso = $response[$i][$k]['id'];
            }
          }
          array_push($aux, [
          'id' => $idCurso,
          'nome' => $nomeCurso,
          'departamento' => $nomeDep,
          'campus'  => $nomeCampus,
          ]);
        }
        $response = $aux;
        return $response;
      }
      else{
        return null;
      }
    }

    public function getAcl($tipoUsusario){
      $response = $this->client->request('GET',$this->api . $this->modulo . '/getAcl/' . $tipoUsusario, [
      'headers' => [
      'Authorization' => session('token_type').' '.session('access_token'),
      'Content-Type' => 'application/json',
      'X-Requested-With' => 'XMLHttpRequest'
      ]
      ]);
      if($response->getStatusCode() == 200){
        $response = json_decode($response->getBody(), true);
        return $response;
      }
      return [''];
    }

    public function loginApi($email, $password){
      try{
        $usuario = $this->client->request('POST', $this->api . 'auth/login', [
        // 'headers' => ['Content-Type' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'],
        'form_params' => ['email' => $email, 'password' => $password],
        ]);

        if($usuario->getStatusCode() == 201){
          $usuario = json_decode($usuario->getBody(), true);
          session(['access_token' => $usuario['access_token'], 'token_type' => $usuario['token_type']]);
          $usuario = $this->client->request('GET', $this->api . 'usuario/getDados/' .             $email, [
          'headers' =>  [
          'Content-Type' => 'application/json',
          'X-Requested-With' => 'XMLHttpRequest',
          'Authorization' => $usuario['token_type'].' '.$usuario['access_token']
          ],
          ]);
          $usuario = json_decode($usuario->getBody(), true);
          // dd($usuario);
          return $usuario;
        }
        else{
          return null;
        }
      }catch(ClientException $e){
        return null;
      }
    }

    public function getEmailsCoordenadorPorCurso($cursoId){
      $response = $this->client->request('GET',$this->api . 'getEmails/' . $cursoId, [
      'headers' => [
      'Content-Type' => 'application/json',
      'X-Requested-With' => 'XMLHttpRequest'
      ]
      ]);
      if($response->getStatusCode() == 201){
        $response = json_decode($response->getBody(), true);
        return $response;
      }
      else{
        return null;
      }
    }

    public function getEmailsPreg(){

      $response = $this->client->request('GET',$this->api . 'getEmails/6',                   [
      'headers' => [
      'Content-Type' => 'application/json',
      'X-Requested-With' => 'XMLHttpRequest'
      ]
      ]);
      if($response->getStatusCode() == 201){
        $response = json_decode($response->getBody(), true);
        return $response;
      }
      else{
        return null;
      }
    }

    public function autorizar($acao){
      $acl = explode(';', session('acl'));
      foreach ($acl as $key) {
        if($key == $acao){
          return true;
        }
      }
      return false;
    }

    public function login($email, $password){
      $user = $this->loginApi($email, $password);
      if(is_null($user)){
        return false;
      }
      else{
        session(['id' => $user['id']]);
        session(['email' => $user['email']]);
        session(['cursoId' => $user['cursoId']]);
        session(['tipo' => $user['tipo']]);
        $acl = $this->getAcl($user['tipoUsuario']);
        $stringAcl = '';
        foreach($acl as $key){
          $stringAcl = $stringAcl . $key . ';';
        }
        session(['acl' => $stringAcl]);
        return true;
      }

    }

}
