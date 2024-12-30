<?php

namespace App\Http\Controllers;

use App\Services\Midea\MideaService;
use Illuminate\Http\Request;

class MideaController extends Controller
{

    private $mideaService;

    public function __construct(MideaService $mideaService) {
        $this->mideaService = $mideaService;
    }

    public function search(Request $request){
        $result = $this->mideaService->search($request);

        return $result;
    }

    public function getByCode($code){
        $result = $this->mideaService->getByCode($code);

        return $this->response($result);
    }

    public function download($code){
        $result = $this->mideaService->download($code);

        return $result;
    }    

    public function create(Request $request){
        $result = $this->mideaService->create($request);

        if($result['status']) $result['message'] = "Mídia criada com sucesso";
        return $this->response($result);
    }

    public function addComment(Request $request){
        $result = $this->mideaService->addComment($request);

        if($result['status']) $result['message'] = "Comentário adicionado com sucesso";
        return $this->response($result);
    }

    public function update(Request $request, $id){
        $result = $this->mideaService->update($request, $id);

        if($result['status']) $result['message'] = "Mídia atualizada com sucesso";
        return $this->response($result);
    }

    public function delete($id){
        $result = $this->mideaService->delete($id);

        if($result['status']) $result['message'] = "Mídia deletada com sucesso";
        return $this->response($result);
    }

    private function response($result){
        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'] ?? null,
            'data' => $result['data'] ?? null,
            'error' => $result['error'] ?? null
        ], $result['statusCode'] ?? 200);
    }
}
